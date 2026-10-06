CREATE TABLE IF NOT EXISTS categories
(
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(255) NOT NULL,
    name        VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug),
    UNIQUE KEY uq_categories_name (name)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles
(
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug         VARCHAR(255) NOT NULL,
    title        VARCHAR(255) NOT NULL,
    description  TEXT         NOT NULL,
    content      MEDIUMTEXT   NOT NULL,
    image        VARCHAR(255) NULL,
    views        INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_articles_slug (slug),
    KEY idx_articles_published_at (published_at),
    KEY idx_articles_views (views)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- An article can belong to several categories
CREATE TABLE IF NOT EXISTS article_category
(
    article_id  INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, category_id),
    KEY idx_article_category_category (category_id, article_id),
    CONSTRAINT fk_article_category_article
        FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE,
    CONSTRAINT fk_article_category_category
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Who has already seen an article today, so reloads and scripts do not inflate the views counter.
-- The visitor is a SHA-256 hash of the IP address and the browser, not the address itself.
CREATE TABLE IF NOT EXISTS article_views
(
    article_id INT UNSIGNED NOT NULL,
    visitor    BINARY(32)   NOT NULL,
    viewed_on  DATE         NOT NULL,
    PRIMARY KEY (article_id, visitor, viewed_on),
    CONSTRAINT fk_article_views_article
        FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
