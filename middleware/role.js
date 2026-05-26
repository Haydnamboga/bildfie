'use strict';

/**
 * requireRole(...roles) → middleware
 *
 * Must be used AFTER requireAuth.
 * Passes if the session user's role is in the allowed list.
 *
 * Usage:
 *   router.post('/admin', requireAuth, requireRole('admin'), handler);
 *   router.post('/bid',   requireAuth, requireRole('professional', 'admin'), handler);
 */
module.exports = function requireRole(...roles) {
  return (req, res, next) => {
    if (!req.session?.userId)
      return res.status(401).json({ error: 'Authentication required' });
    if (!roles.includes(req.session.userRole))
      return res.status(403).json({ error: `Access restricted to: ${roles.join(', ')}` });
    next();
  };
};
