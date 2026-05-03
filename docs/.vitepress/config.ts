import { defineConfig } from 'vitepress'

export default defineConfig({
  lang: 'es-GT',
  title: 'infile-php',
  description: 'SDK PHP para Factura Electrónica en Línea (FEL) de Guatemala via Infile S.A.',
  base: '/infile-php/',

  head: [
    ['meta', { name: 'author', content: 'Rodmar Zavala' }],
    ['meta', { name: 'keywords', content: 'FEL, Guatemala, factura electrónica, SAT, Infile, PHP, Laravel, Symfony' }],
    ['meta', { property: 'og:title', content: 'infile-php — SDK FEL Guatemala' }],
    ['meta', { property: 'og:description', content: 'SDK PHP para Factura Electrónica en Línea de Guatemala via Infile S.A.' }],
    ['link', { rel: 'icon', href: '/infile-php/favicon.ico' }],
  ],

  themeConfig: {
    logo: '/logo.svg',
    siteTitle: 'infile-php',

    nav: [
      { text: 'Guía', link: '/guia/inicio-rapido' },
      { text: 'API', link: '/api/invoice' },
      { text: 'Changelog', link: '/changelog' },
      {
        text: 'v1.0.5',
        items: [
          { text: 'v1.0.5 (actual)', link: '/changelog' },
          { text: 'Hoja de ruta', link: '/hoja-de-ruta' },
        ],
      },
    ],

    sidebar: {
      '/guia/': [
        {
          text: 'Introducción',
          items: [
            { text: '¿Qué es infile-php?', link: '/guia/introduccion' },
            { text: 'Instalación', link: '/guia/instalacion' },
            { text: 'Inicio rápido', link: '/guia/inicio-rapido' },
            { text: 'Credenciales', link: '/guia/credenciales' },
          ],
        },
        {
          text: 'Tipos de DTE',
          items: [
            { text: 'Factura estándar (FACT)', link: '/guia/dte/factura' },
            { text: 'Nota de crédito (NCRE)', link: '/guia/dte/nota-credito' },
            { text: 'Nota de débito (NDEB)', link: '/guia/dte/nota-debito' },
            { text: 'Pequeño contribuyente (FPEQ)', link: '/guia/dte/pequeno-contribuyente' },
            { text: 'Todos los tipos', link: '/guia/dte/tipos' },
          ],
        },
        {
          text: 'Funcionalidades',
          items: [
            { text: 'Consultas RTU / CUI', link: '/guia/rtu-cui' },
            { text: 'Anulación de DTEs', link: '/guia/anulacion' },
            { text: 'Validación XSD', link: '/guia/validacion-xsd' },
            { text: 'Contingencia CAFE', link: '/guia/contingencia' },
          ],
        },
        {
          text: 'Adaptadores',
          items: [
            { text: 'Laravel', link: '/guia/laravel' },
            { text: 'Symfony', link: '/guia/symfony' },
            { text: 'WordPress / WooCommerce', link: '/guia/wordpress' },
          ],
        },
        {
          text: 'Pruebas',
          items: [
            { text: 'FelFake', link: '/guia/felfake' },
            { text: 'Fixtures de prueba', link: '/guia/fixtures' },
          ],
        },
      ],
      '/api/': [
        {
          text: 'Referencia de API',
          items: [
            { text: 'Invoice', link: '/api/invoice' },
            { text: 'CreditNote', link: '/api/credit-note' },
            { text: 'DebitNote', link: '/api/debit-note' },
            { text: 'SmallTaxpayerInvoice', link: '/api/small-taxpayer-invoice' },
            { text: 'Recipient', link: '/api/recipient' },
            { text: 'Item', link: '/api/item' },
            { text: 'CertificationResponse', link: '/api/certification-response' },
            { text: 'Rtu', link: '/api/rtu' },
            { text: 'FelFake', link: '/api/felfake' },
          ],
        },
        {
          text: 'Excepciones',
          items: [
            { text: 'Jerarquía de excepciones', link: '/api/excepciones' },
          ],
        },
      ],
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/rodmarzavala/infile-php' },
    ],

    footer: {
      message: 'Licencia MIT',
      copyright: 'Copyright © 2024 Rodmar Zavala',
    },

    editLink: {
      pattern: 'https://github.com/rodmarzavala/infile-php/edit/main/docs/:path',
      text: 'Editar esta página en GitHub',
    },

    lastUpdated: {
      text: 'Última actualización',
    },

    search: {
      provider: 'local',
    },

    docFooter: {
      prev: 'Página anterior',
      next: 'Página siguiente',
    },

    outline: {
      label: 'En esta página',
      level: [2, 3],
    },
  },

  markdown: {
    theme: {
      light: 'github-light',
      dark: 'github-dark',
    },
  },
})
