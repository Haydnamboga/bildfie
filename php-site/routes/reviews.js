'use strict';
const express    = require('express');
const router     = express.Router();
const { body }   = require('express-validator');
const db         = require('../db/knex');
const requireAuth = require('../middleware/auth');
const validate   = require('../middleware/validate');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

const reviewRules = validate([
  body('reviewee_id').isInt({ min: 1 }).withMessage('Valid professional ID required'),
  body('rating').isInt({ min: 1, max: 5 }).withMessage('Rating must be 1–5'),
  body('comment').optional().trim().isLength({ max: 1000 }).withMessage('Comment too long'),
  body('project_id').optional().isInt({ min: 1 }),
]);

// GET /api/reviews/professional/:id — reviews for a specific professional
router.get('/professional/:id', wrap(async (req, res) => {
  const rows = await db('reviews as r')
    .join('users as u', 'r.reviewer_id', 'u.id')
    .where('r.reviewee_id', req.params.id)
    .orderBy('r.created_at', 'desc')
    .select(
      'r.id', 'r.rating', 'r.comment', 'r.created_at', 'r.project_id',
      'u.id as reviewer_id', 'u.name as reviewer_name', 'u.avatar as reviewer_avatar'
    );

  const [{ avg, cnt }] = await db('reviews')
    .where({ reviewee_id: req.params.id })
    .avg('rating as avg')
    .count('id as cnt');

  res.json({
    reviews: rows,
    average_rating: avg ? Math.round(Number(avg) * 10) / 10 : null,
    total: Number(cnt),
  });
}));

// POST /api/reviews — submit a review
router.post('/', requireAuth, reviewRules, wrap(async (req, res) => {
  const { reviewee_id, rating, comment, project_id } = req.body;

  // Can't review yourself
  if (Number(reviewee_id) === req.session.userId)
    return res.status(400).json({ error: 'You cannot review yourself' });

  // One review per reviewer-reviewee pair (optional: uncomment to enforce)
  // const existing = await db('reviews')
  //   .where({ reviewer_id: req.session.userId, reviewee_id }).first('id');
  // if (existing) return res.status(409).json({ error: 'You have already reviewed this professional' });

  const id = await db.insertId('reviews', {
    reviewer_id: req.session.userId,
    reviewee_id,
    rating,
    comment: comment || null,
    project_id: project_id || null,
  });

  // Recalculate and persist the professional's average rating
  const [{ avg, cnt }] = await db('reviews')
    .where({ reviewee_id })
    .avg('rating as avg')
    .count('id as cnt');
  await db('users').where({ id: reviewee_id }).update({
    rating:       Math.round(Number(avg) * 10) / 10,
    review_count: Number(cnt),
  });

  res.status(201).json({ id, message: 'Review submitted' });
}));

module.exports = router;
