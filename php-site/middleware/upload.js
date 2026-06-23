'use strict';
/**
 * Multer upload configurations
 *
 * avatarUpload   — single image file, max 5 MB, stored in uploads/avatars/
 * documentUpload — image or PDF, max 10 MB, stored in uploads/documents/
 *
 * Filenames are randomised with uuid to prevent path-traversal collisions.
 */

const multer = require('multer');
const path   = require('path');
const fs     = require('fs');
const { v4: uuidv4 } = require('uuid');

const UPLOAD_ROOT = path.join(__dirname, '..', process.env.UPLOAD_DIR || 'uploads');

/* ── Ensure directories exist at module load time ─────────────────── */
['avatars', 'documents'].forEach(sub => {
  const dir = path.join(UPLOAD_ROOT, sub);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

/* ── Storage factory ────────────────────────────────────────────────── */
function diskStorage(sub) {
  return multer.diskStorage({
    destination: (_req, _file, cb) => cb(null, path.join(UPLOAD_ROOT, sub)),
    filename:    (_req, file, cb) => {
      const ext = path.extname(file.originalname).toLowerCase();
      cb(null, `${uuidv4()}${ext}`);
    },
  });
}

/* ── MIME filters ───────────────────────────────────────────────────── */
const IMAGE_MIME   = /^image\/(jpeg|png|webp|gif)$/;
const DOC_MIME     = /^(image\/(jpeg|png|webp)|application\/pdf)$/;

function imageFilter(_req, file, cb) {
  IMAGE_MIME.test(file.mimetype)
    ? cb(null, true)
    : cb(Object.assign(new Error('Only JPEG, PNG, WebP or GIF images are allowed'), { status: 415 }));
}

function documentFilter(_req, file, cb) {
  DOC_MIME.test(file.mimetype)
    ? cb(null, true)
    : cb(Object.assign(new Error('Only JPEG, PNG, WebP images or PDF files are allowed'), { status: 415 }));
}

/* ── Exported multer instances ──────────────────────────────────────── */
const MAX_AVATAR_MB = 5;
const MAX_DOC_MB    = Number(process.env.MAX_FILE_SIZE_MB) || 10;

const avatarUpload = multer({
  storage:  diskStorage('avatars'),
  fileFilter: imageFilter,
  limits:   { fileSize: MAX_AVATAR_MB * 1024 * 1024, files: 1 },
});

const documentUpload = multer({
  storage:  diskStorage('documents'),
  fileFilter: documentFilter,
  limits:   { fileSize: MAX_DOC_MB * 1024 * 1024, files: 5 },
});

/**
 * Wrap a multer middleware so multer errors are forwarded to express
 * error handler rather than crashing with an unhandled rejection.
 */
function wrapUpload(multerMiddleware) {
  return (req, res, next) =>
    multerMiddleware(req, res, err => {
      if (!err) return next();
      if (err.code === 'LIMIT_FILE_SIZE')
        return res.status(413).json({ error: `File too large — max ${MAX_DOC_MB} MB` });
      err.status = err.status || 400;
      next(err);
    });
}

module.exports = { avatarUpload, documentUpload, wrapUpload };
