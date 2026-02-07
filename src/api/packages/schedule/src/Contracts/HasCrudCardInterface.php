<?php

namespace Backpack\Schedule\Contracts;

/**
 * Интерфейс для моделей, которые могут отображать карточку в CRUD списках
 * Используется для отображения связанных записей в ScheduledPublicationCrudController и других CRUD
 */
interface HasCrudCardInterface
{
    /**
     * Получить HTML карточки для отображения в CRUD списке
     * 
     * @param array $options Дополнительные опции (например, compact, showRating и т.д.)
     * @return string HTML карточки
     */
    public function getCrudCardHtml(array $options = []): string;

    /**
     * Получить URL для редактирования записи в админке
     * 
     * @return string|null URL или null если недоступно
     */
    public function getCrudEditUrl(): ?string;

    /**
     * Получить название записи для отображения
     * 
     * @return string
     */
    public function getCrudCardTitle(): string;
}
