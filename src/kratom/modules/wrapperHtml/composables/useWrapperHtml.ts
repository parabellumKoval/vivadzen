export function useWrapperHtml() {
  /**
   * Парсит шаблонизированную строку и оборачивает содержимое [[-- --]] в HTML теги
   * @param {string} text - Исходный текст с шаблонами вида "For [[--Viva--]] Points [[--mining--]]"
   * @param {string[]} wrappers - Массив HTML тегов для оборачивания (например: ['<span class="orange">', '<b>'])
   * @returns {string} - Обработанный HTML или исходный текст без шаблонов
   */
  function wrapperHtml(text: string, wrappers: string[] = []): string {
    if (!text || typeof text !== 'string') {
      return ''
    }

    // Регулярное выражение для поиска шаблонов [[--content--]]
    const templateRegex = /\[\[--([^\]]+)--\]\]/g
    const matches = []
    let match

    // Находим все совпадения
    while ((match = templateRegex.exec(text)) !== null) {
      matches.push({
        fullMatch: match[0],
        content: match[1],
        index: match.index
      })
    }

    // Если нет шаблонов, возвращаем исходный текст
    if (matches.length === 0) {
      return text
    }

    // Если нет обертывающих тегов или их недостаточно, возвращаем текст без шаблонов
    if (!wrappers || wrappers.length === 0) {
      let result = text
      matches.reverse().forEach(match => {
        result = result.substring(0, match.index) + match.content + result.substring(match.index + match.fullMatch.length)
      })
      return result
    }

    let result = text
    let offset = 0

    // Обрабатываем каждое совпадение
    matches.forEach((match, index) => {
      const wrapperIndex = index % wrappers.length
      const openTag = wrappers[wrapperIndex]
      
      // Извлекаем имя тега для создания закрывающего тега
      const tagMatch = openTag.match(/<(\w+)/)
      const tagName = tagMatch ? tagMatch[1] : 'span'
      const closeTag = `</${tagName}>`
      
      const wrappedContent = `${openTag}${match.content}${closeTag}`
      const startPos = match.index + offset
      const endPos = startPos + match.fullMatch.length
      
      result = result.substring(0, startPos) + wrappedContent + result.substring(endPos)
      
      // Обновляем смещение для следующих замен
      offset += wrappedContent.length - match.fullMatch.length
    })

    return result
  }

  return {
    wrapperHtml
  }
}

// Экспортируем функцию напрямую для удобства использования
export function wrapperHtml(text: string, wrappers: string[] = []): string {
  const { wrapperHtml: fn } = useWrapperHtml()
  return fn(text, wrappers)
}