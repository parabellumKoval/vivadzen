import { describe, expect, it, vi, beforeEach } from 'vitest'

import { normalizeKratomProductImage, normalizeKratomProductImages, resolveKratomProductImageSrc } from './productImage'

describe('productImage utils', () => {
  beforeEach(() => {
    vi.stubGlobal('useImg', () => ({
      noImage: '/images/noimage.png',
      folderSrc: (src: string, folder: string) => `/${folder}/${src}`,
    }))
  })

  it('prefixes relative product image paths', () => {
    expect(normalizeKratomProductImage({ src: 'example.png' }, 'Example')).toMatchObject({
      src: '/products/example.png',
      alt: 'Example',
      title: 'Example',
    })
  })

  it('keeps absolute image urls unchanged', () => {
    expect(normalizeKratomProductImage({ src: 'https://cdn.example.com/example.png', alt: 'Alt' }, 'Example')).toMatchObject({
      src: 'https://cdn.example.com/example.png',
      alt: 'Alt',
      title: 'Alt',
    })
  })

  it('supports string image arrays and falls back to the first image', () => {
    expect(resolveKratomProductImageSrc({
      name: 'Example',
      images: ['gallery/example.png'],
    })).toBe('/products/gallery/example.png')
  })

  it('returns placeholder when images are missing', () => {
    expect(normalizeKratomProductImages([], 'Example')).toEqual([{
      src: '/images/noimage.png',
      alt: 'Example',
      title: 'Example',
    }])
  })
})
