'use strict';
const express    = require('express');
const router     = express.Router();
const { body }   = require('express-validator');
const db         = require('../db/knex');
const requireAuth = require('../middleware/auth');
const validate   = require('../middleware/validate');

const wrap = fn => (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);

const addRules = validate([
  body('material_id').isInt({ min: 1 }).withMessage('Valid material ID required'),
  body('quantity').optional().isInt({ min: 1, max: 9999 }).withMessage('Quantity must be 1–9999'),
]);

const updateRules = validate([
  body('quantity').isInt({ min: 1, max: 9999 }).withMessage('Quantity must be 1–9999'),
]);

// GET /api/cart — current user's cart with material details
router.get('/', requireAuth, wrap(async (req, res) => {
  const rows = await db('cart as c')
    .join('materials as m', 'c.material_id', 'm.id')
    .where('c.user_id', req.session.userId)
    .orderBy('c.added_at', 'desc')
    .select(
      'c.id', 'c.quantity', 'c.added_at',
      'm.id as material_id', 'm.name', 'm.category', 'm.price',
      'm.currency', 'm.unit', 'm.stock_status', 'm.image_url',
      'm.supplier_name', 'm.delivery_speed'
    );

  const subtotal = rows.reduce((sum, r) => sum + r.price * r.quantity, 0);
  res.json({ items: rows, subtotal, count: rows.length });
}));

// POST /api/cart — add item (or increment if already in cart)
router.post('/', requireAuth, addRules, wrap(async (req, res) => {
  const { material_id, quantity = 1 } = req.body;

  // Confirm material exists
  const mat = await db('materials').where({ id: material_id }).first('id', 'stock_status');
  if (!mat) return res.status(404).json({ error: 'Material not found' });
  if (mat.stock_status === 'out') return res.status(400).json({ error: 'Item is out of stock' });

  // Upsert: if the row exists bump quantity, otherwise insert
  const existing = await db('cart')
    .where({ user_id: req.session.userId, material_id })
    .first('id', 'quantity');

  if (existing) {
    await db('cart').where({ id: existing.id }).update({ quantity: existing.quantity + quantity });
    return res.json({ id: existing.id, quantity: existing.quantity + quantity, updated: true });
  }

  const id = await db.insertId('cart', {
    user_id:     req.session.userId,
    material_id,
    quantity,
  });
  res.status(201).json({ id, quantity, updated: false });
}));

// PUT /api/cart/:id — update quantity
router.put('/:id', requireAuth, updateRules, wrap(async (req, res) => {
  const item = await db('cart')
    .where({ id: req.params.id, user_id: req.session.userId })
    .first('id');
  if (!item) return res.status(404).json({ error: 'Cart item not found' });
  await db('cart').where({ id: req.params.id }).update({ quantity: req.body.quantity });
  res.json({ message: 'Updated' });
}));

// DELETE /api/cart/:id — remove one item
router.delete('/:id', requireAuth, wrap(async (req, res) => {
  const deleted = await db('cart')
    .where({ id: req.params.id, user_id: req.session.userId })
    .delete();
  if (!deleted) return res.status(404).json({ error: 'Cart item not found' });
  res.json({ message: 'Removed' });
}));

// DELETE /api/cart — clear entire cart
router.delete('/', requireAuth, wrap(async (req, res) => {
  const count = await db('cart').where({ user_id: req.session.userId }).delete();
  res.json({ message: 'Cart cleared', removed: count });
}));

module.exports = router;
