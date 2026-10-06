{extends file='layout.tpl'}

{block name=title}{$category.name} — {$smarty.block.parent}{/block}

{block name=content}
    <nav class="breadcrumbs" aria-label="Навигация">
        <a href="/">Главная</a>
        <span>{$category.name}</span>
    </nav>

    <header class="page-header">
        <h1 class="page-header__title">{$category.name}</h1>
        <p class="page-header__text">{$category.description}</p>
    </header>

    {if $articles}
        <div class="toolbar">
            <span class="toolbar__count">Статей: {$paginator->total}</span>
            <nav class="sort" aria-label="Сортировка">
                <span class="sort__label">Сортировать:</span>
                {foreach $sorts as $option}
                    <a class="sort__link{if $option === $sort} sort__link--active{/if}"
                       href="?sort={$option->value}"{if $option === $sort} aria-current="true"{/if}>{$option->label()}</a>
                {/foreach}
            </nav>
        </div>

        <div class="cards">
            {foreach $articles as $article}
                {include file='partials/article-card.tpl'}
            {/foreach}
        </div>

        {include file='partials/pagination.tpl'}
    {else}
        <p class="empty">В этой категории пока нет статей.</p>
    {/if}
{/block}
