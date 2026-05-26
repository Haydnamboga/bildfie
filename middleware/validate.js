'use strict';
const { validationResult } = require('express-validator');

/**
 * validate(rules) → middleware[]
 *
 * Wraps express-validator rules with an automatic error-response handler.
 * Returns the first validation error as { error: '...' } with status 400.
 *
 * Usage:
 *   router.post('/login', validate([
 *     body('email').isEmail(),
 *     body('password').notEmpty(),
 *   ]), wrap(async (req, res) => { ... }));
 */
module.exports = function validate(rules) {
  return [
    ...rules,
    (req, res, next) => {
      const errors = validationResult(req);
      if (!errors.isEmpty())
        return res.status(400).json({ error: errors.array()[0].msg });
      next();
    },
  ];
};
