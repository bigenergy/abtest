{extends file='layout.tpl'}

{block name=content}
    <div class="hero">
        <h1 class="hero__title">Заметки о веб-разработке</h1>
        <p class="hero__text">PHP, базы данных, фронтенд, DevOps и всё, что между ними.</p>
    </div>

    {foreach $sections as $section}
        <section class="section">
            <div class="section__header">
                <div>
                    <h2 class="section__title">
                        <a href="/category/{$section.category.slug}">{$section.category.name}</a>
                    </h2>
                    <p class="section__description">{$section.category.description}</p>
                </div>
                <a class="button" href="/category/{$section.category.slug}">Все статьи</a>
            </div>

            <div class="cards">
                {foreach $section.articles as $article}
                    {include file='partials/article-card.tpl'}
                {/foreach}
            </div>
        </section>
    {foreachelse}
        <p class="empty">Статей пока нет. Заполните базу командой <code>php bin/seed.php</code></p>
    {/foreach}
{/block}
