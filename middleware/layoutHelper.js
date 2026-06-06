'use strict';
const path = require('path');
const fs = require('fs');
const ejs = require('ejs');

/**
 * Wrap res.render so that views can specify a layout.
 * Usage in a view: set locals.layout = 'field' | 'church' | 'public' | false
 */
module.exports = function layoutHelper(req, res, next) {
  const origRender = res.render.bind(res);

  res.render = function(view, locals, callback) {
    if (typeof locals === 'function') { callback = locals; locals = {}; }
    locals = locals || {};

    // Attach path for active nav detection
    locals.path = req.path;

    // Determine layout from locals or view path prefix
    let layout = locals.layout;
    if (layout === undefined) {
      if (view.startsWith('field/')) layout = 'field';
      else if (view.startsWith('church/')) layout = 'church';
      else if (view.startsWith('home/') || view.startsWith('portal/') || view.startsWith('auth/')) layout = 'public';
      else layout = false;
    }

    if (!layout) return origRender(view, locals, callback);

    // Render the body view first, then wrap in layout
    origRender(view, { ...locals, layout: false }, (err, body) => {
      if (err) return callback ? callback(err) : next(err);
      origRender(`layouts/${layout}`, { ...locals, body }, callback);
    });
  };

  next();
};
