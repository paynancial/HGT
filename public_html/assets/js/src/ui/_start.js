/* Holiday Guru Travel — Phase 1 UI behaviour. No dependencies. */
(function () {
    'use strict';

    var desktop = window.matchMedia('(min-width: 1024px)');
    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

