import { defineConfig } from 'vitest/config'
import { fileURLToPath } from 'node:url'
import path from 'node:path'
import vue from '@vitejs/plugin-vue'

const rootDir = fileURLToPath(new URL('.', import.meta.url))

export default defineConfig({
  plugins: [
    vue({
      customBlockTransforms: {
        i18n: () => ({
          code: 'export default {}',
        }),
      },
    }),
    {
      name: 'stub-i18n-loader',
      transform(code, id) {
        if (id.includes('?vue&type=i18n')) {
          return { code: 'export default {}', map: null }
        }
        if (id.endsWith('.yaml')) {
          return { code: 'export default {}', map: null }
        }
        return null
      },
    },
  ],
  test: {
    globals: true,
    environment: 'happy-dom',
  },
  resolve: {
    alias: {
      '~': rootDir,
      '~~': rootDir,
      '@': rootDir,
    },
  },
})
