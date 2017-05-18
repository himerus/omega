'use strict';
// Docs: http://stylelint.io
// Style lint rule detail: https://github.com/stylelint/stylelint/tree/master/src/rules/RULE-NAME

module.exports = {
  plugins: [
    "stylelint-scss",
  ],
  ignoreFiles: [],
  rules: {
    "declaration-colon-space-after": "always",
    "declaration-no-important": true,
    "indentation": 2,
    // @todo: Research options here to fix these lint issues.
    "max-nesting-depth": 4, // 3 is absolutely ridiculous. .thing ul li a should be FINE
    // Format is "id,class,type", as laid out in the W3C selector spec.
    // @see https://drafts.csswg.org/selectors/#specificity-rules
    // @see http://cssguidelin.es/#specificity
    "selector-max-specificity": "0,3,3",
    "selector-no-id": true,
    "scss/at-extend-no-missing-placeholder": true,
    "scss/selector-no-redundant-nesting-selector": true,
    "at-rule-no-vendor-prefix": true,
    "media-feature-name-no-vendor-prefix": true,
    "property-no-vendor-prefix": true,
    "selector-no-vendor-prefix": true,
    "value-no-vendor-prefix": true,
  },
};
