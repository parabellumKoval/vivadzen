export default defineEventHandler(async (event) => {
  try {
    // Тестируем запись в кеш
    const testData = {
      message: 'Hello from cache!',
      timestamp: Date.now(),
      random: Math.random()
    }
    
    const storage = useStorage('cache')
    
    // Записываем тестовые данные
    await storage.setItem('test:cache-check', testData)
    // Читаем данные обратно
    const readData = await storage.getItem('test:cache-check')
    // Проверяем настройки storage
    const hasUpstash = !!process.env.UPSTASH_REDIS_REST_URL
    const driver = hasUpstash ? 'upstash' : 'memory'
    
    return {
      success: true,
      driver,
      hasUpstash,
      written: testData,
      read: readData,
      match: JSON.stringify(testData) === JSON.stringify(readData),
      upstashUrl: process.env.UPSTASH_REDIS_REST_URL ? 'configured' : 'not configured'
    }
  } catch (error) {
    console.error('[cache-test] Error:', error)
    return {
      success: false,
      error: error.message,
      driver: process.env.UPSTASH_REDIS_REST_URL ? 'upstash' : 'memory'
    }
  }
})