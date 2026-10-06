{extends file='layout.tpl'}

{block name=title}{$article.title} — {$smarty.block.parent}{/block}

{block name=content}
    <article class="article">
        <nav class="breadcrumbs" aria-label="Навигация">
            <a href="/">Главная</a>
            <span>{$article.title}</span>
        </nav>

        <header class="article__header">
            <ul class="tags">
                {foreach $categories as $category}
                    <li><a class="tag" href="/category/{$category.id}">{$category.name}</a></li>
                {/foreach}
            </ul>
            <h1 class="article__title">{$article.title}</h1>
            <p class="article__lead">{$article.description}</p>
            <div class="meta">
                <time datetime="{$article.published_at|date_format:'Y-m-d'}">{$article.published_at|date_ru}</time>
                <span class="views">Просмотров: {$article.views}</span>
            </div>
        </header>

        {if $article.image}
            <img class="article__cover" src="{$article.image}" alt="">
        {/if}

        <div class="article__content">
            {foreach $article.content|paragraphs as $paragraph}
                <p>{$paragraph}</p>
            {/foreach}
        </div>
    </article>
{/block}
