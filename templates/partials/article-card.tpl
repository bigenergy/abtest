<article class="card">
    <a class="card__cover" href="/article/{$article.slug}" tabindex="-1" aria-hidden="true">
        {if $article.image}
            <img src="{$article.image}" alt="" loading="lazy">
        {/if}
    </a>
    <div class="card__body">
        <h3 class="card__title">
            <a href="/article/{$article.slug}">{$article.title}</a>
        </h3>
        <p class="card__description">{$article.description}</p>
        <div class="card__meta meta">
            <time datetime="{$article.published_at|date_format:'Y-m-d'}">{$article.published_at|date_ru}</time>
            <span class="views" title="Просмотры">
                <span class="visually-hidden">Просмотров:</span> {$article.views}
            </span>
        </div>
    </div>
</article>
