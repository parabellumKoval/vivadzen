import type { Config } from 'tailwindcss'

export default <Partial<Config>>{
  content: [
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './app.vue',
  ],
  theme: {
    extend: {
      colors: {
        // Палитра Vivadzen из vivadzen-design-tz/01_DESIGN_SYSTEM
        moss:       { 50: '#f3f6f0', 500: '#5a7a3f', 700: '#3e5728', 900: '#26341a' },
        terracotta: { 500: '#b85b4a', 700: '#8c4337' },
        cream:      { 50: '#fbf8f1', 100: '#f6f1e3' },
        ink:        { 700: '#2a2a2a', 900: '#111111' },
      },
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        sans:    ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
}
