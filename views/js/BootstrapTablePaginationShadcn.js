/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

class BootstrapTablePaginationShadcn {
  constructor(options = {}) {
    this.styleId = options.styleId || 'bs-table-pagination-shadcn-styles';
  }

  static init(options = {}) {
    const instance = new BootstrapTablePaginationShadcn(options);
    instance.init();
    return instance;
  }

  init() {
    this.injectStyles();
    this.bindEvents();
  }

  injectStyles() {
    if (document.getElementById(this.styleId)) {
      return;
    }

    const styleContent = `
      .table-responsive,
      .bootstrap-table,
      .fixed-table-pagination {
        overflow: visible !important;
      }

      .fixed-table-pagination {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-top: 15px !important;
        clear: both !important;
      }

      .fixed-table-pagination .pagination-detail,
      .fixed-table-pagination .page-list {
        display: inline-flex !important;
        align-items: center !important;
        white-space: nowrap !important;
      }

      .fixed-table-pagination .btn-group,
      .fixed-table-pagination .dropup {
        display: inline-block !important;
        position: relative !important;
        vertical-align: middle !important;
        margin: 0 6px !important;
        overflow: visible !important;
      }

      .bootstrap-table .dropdown-menu {
        display: none;
        position: absolute !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 6px !important;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
        min-width: 90px !important;
        z-index: 999999 !important;
        animation: shadcnDropdownFade 0.15s ease-out !important;
      }

      .bootstrap-table .dropup > .dropdown-menu,
      .bootstrap-table .btn-group.dropup > .dropdown-menu,
      .bootstrap-table .page-list .btn-group > .dropdown-menu {
        bottom: 100% !important;
        top: auto !important;
        left: 0 !important;
        right: auto !important;
        margin-bottom: 6px !important;
        margin-top: 0 !important;
      }

      .bootstrap-table .btn-group.open > .dropdown-menu,
      .bootstrap-table .btn-group.show > .dropdown-menu,
      .bootstrap-table .dropup.open > .dropdown-menu,
      .bootstrap-table .dropup.show > .dropdown-menu,
      .bootstrap-table .dropdown.open > .dropdown-menu,
      .bootstrap-table .dropdown.show > .dropdown-menu {
        display: block !important;
      }

      @keyframes shadcnDropdownFade {
        from {
          opacity: 0;
          transform: translateY(4px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .bootstrap-table .dropdown-menu .dropdown-item,
      .bootstrap-table .dropdown-menu > li > a {
        color: #334155 !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        padding: 8px 14px !important;
        border-radius: 6px !important;
        transition: all 0.15s ease !important;
        display: block !important;
        margin-bottom: 2px !important;
        text-decoration: none !important;
      }

      .bootstrap-table .dropdown-menu .dropdown-item:last-child,
      .bootstrap-table .dropdown-menu > li:last-child > a {
        margin-bottom: 0 !important;
      }

      .bootstrap-table .dropdown-menu .dropdown-item:hover,
      .bootstrap-table .dropdown-menu .dropdown-item:focus,
      .bootstrap-table .dropdown-menu > li > a:hover,
      .bootstrap-table .dropdown-menu > li > a:focus {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        outline: none !important;
      }

      .bootstrap-table .dropdown-menu .dropdown-item.active,
      .bootstrap-table .dropdown-menu .dropdown-item:active,
      .bootstrap-table .dropdown-menu > li.active > a {
        background-color: #0f172a !important;
        color: #ffffff !important;
        font-weight: 600 !important;
      }

      .bootstrap-table .fixed-table-pagination .pagination,
      .bootstrap-table .pagination {
        display: inline-flex !important;
        align-items: center !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 4px !important;
        border: none !important;
      }

      .bootstrap-table .pagination .page-item,
      .bootstrap-table .pagination li {
        border-radius: 8px !important;
        background: transparent !important;
        border: none !important;
        margin: 0 2px !important;
        overflow: hidden !important;
      }

      .bootstrap-table .pagination .page-item .page-link,
      .bootstrap-table .pagination .page-item > a,
      .bootstrap-table .pagination li > a {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 36px !important;
        height: 36px !important;
        padding: 0 10px !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        color: #334155 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        text-decoration: none !important;
        transition: all 0.15s ease-in-out !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
      }

      .bootstrap-table .pagination .page-item .page-link:hover,
      .bootstrap-table .pagination .page-item > a:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
        text-decoration: none !important;
      }

      .bootstrap-table .pagination li.page-item.active,
      .bootstrap-table .pagination li.active,
      .bootstrap-table.bootstrap5 .pagination li.page-item.active {
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        border-radius: 8px !important;
      }

      .bootstrap-table .pagination li.page-item.active > a,
      .bootstrap-table .pagination li.page-item.active > a.page-link,
      .bootstrap-table .pagination li.page-item.active span,
      .bootstrap-table .pagination li.active > a,
      .bootstrap-table.bootstrap5 .pagination li.page-item.active > a,
      .bootstrap-table.bootstrap5 .pagination li.page-item.active > a.page-link,
      .bootstrap-table.bootstrap5 .pagination li.active > a,
      div.fixed-table-pagination div.pagination ul.pagination li.page-item.active > a,
      div.fixed-table-pagination div.pagination ul.pagination li.page-item.active > a.page-link {
        background-color: #0f172a !important;
        background: #0f172a !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid #0f172a !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25) !important;
      }

      .bootstrap-table .pagination li.page-item.active > a:hover,
      .bootstrap-table .pagination li.page-item.active > a:focus,
      .bootstrap-table .pagination li.page-item.active > a:active,
      .bootstrap-table.bootstrap5 .pagination li.page-item.active > a:hover,
      .bootstrap-table.bootstrap5 .pagination li.page-item.active > a:focus,
      .bootstrap-table.bootstrap5 .pagination li.page-item.active > a:active {
        background-color: #0f172a !important;
        background: #0f172a !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid #0f172a !important;
        border-radius: 8px !important;
      }

      .bootstrap-table .pagination .page-item.disabled .page-link,
      .bootstrap-table .pagination .page-item.disabled > a {
        background-color: #f8fafc !important;
        color: #cbd5e1 !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
        opacity: 0.7 !important;
      }

      .bootstrap-table .pagination .page-item.page-last-separator .page-link,
      .bootstrap-table .pagination .page-item.page-first-separator .page-link {
        border-color: transparent !important;
        background-color: transparent !important;
        color: #64748b !important;
        cursor: default !important;
      }
    `;

    const styleEl = document.createElement('style');
    styleEl.id = this.styleId;
    styleEl.type = 'text/css';
    styleEl.textContent = styleContent;
    document.head.appendChild(styleEl);
  }

  bindEvents() {
    if (typeof $ === 'undefined') return;

    $(document).on('click', '.bootstrap-table .page-list button, .bootstrap-table .page-list .dropdown-toggle', function (e) {
      e.preventDefault();
      e.stopPropagation();
      let group = $(this).closest('.btn-group, .dropup, .dropdown');
      if (!group.length) {
        group = $(this).parent();
      }
      const isAlreadyOpen = group.hasClass('open') || group.hasClass('show');
      $('.bootstrap-table .btn-group, .bootstrap-table .dropup, .bootstrap-table .dropdown').removeClass('open show');
      if (!isAlreadyOpen) {
        group.addClass('open show');
      }
    });

    $(document).on('click', function (e) {
      if (!$(e.target).closest('.btn-group, .dropup, .dropdown').length) {
        $('.bootstrap-table .btn-group, .bootstrap-table .dropup, .bootstrap-table .dropdown').removeClass('open show');
      }
    });
  }
}

if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', () => {
    BootstrapTablePaginationShadcn.init();
  });
}
