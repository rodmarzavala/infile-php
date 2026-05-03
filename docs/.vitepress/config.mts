import { defineConfig } from 'vitepress'

export default defineConfig({
  title: "Infile PHP SDK",
  description: "SDK PHP moderno y elegante para la Factura Electrónica en Línea (FEL) de Guatemala.",
  lang: 'es-GT',
  base: '/infile-php/',
  cleanUrls: true,

  themeConfig: {
    logo: '/logo.png', // Opcional, puedes agregar un logo luego
    nav: [
      { text: 'Inicio', link: '/' },
      { text: 'Guía', link: '/guide/getting-started' },
      { text: 'Referencia API', link: '/api/overview' }
    ],

    sidebar: [
      {
        text: 'Introducción',
        items: [
          { text: '¿Qué es infile-php?', link: '/guide/what-is' },
          { text: 'Instalación', link: '/guide/getting-started' },
          { text: 'Ejemplos de uso', link: '/guide/examples' }
        ]
      },
      {
        text: 'Integraciones',
        items: [
          { text: 'Laravel', link: '/integrations/laravel' },
          { text: 'Symfony', link: '/integrations/symfony' },
          { text: 'WordPress / WooCommerce', link: '/integrations/wordpress' }
        ]
      },
      {
        text: 'Avanzado',
        items: [
          { text: 'Consultas RTU y CUI', link: '/advanced/rtu-cui' },
          { text: 'Pruebas con FelFake', link: '/advanced/testing' }
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/rodmarzavala/infile-php' }
    ],

    footer: {
      message: 'Lanzado bajo licencia MIT.',
      copyright: 'Copyright © 2024 Rodmar Zavala y contribuyentes'
    },

    search: {
      provider: 'local',
      options: {
        locales: {
          root: {
            translations: {
              button: {
                buttonText: 'Buscar',
                buttonAriaLabel: 'Buscar'
              },
              modal: {
                noResultsText: 'No se encontraron resultados para',
                resetButtonTitle: 'Borrar búsqueda',
                footer: {
                  selectText: 'para seleccionar',
                  navigateText: 'para navegar',
                  closeText: 'para cerrar'
                }
              }
            }
          }
        }
      }
    }
  }
})
