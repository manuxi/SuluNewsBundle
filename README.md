# SuluNewsBundle!
![php workflow](https://github.com/manuxi/SuluNewsBundle/actions/workflows/php.yml/badge.svg)
![symfony workflow](https://github.com/manuxi/SuluNewsBundle/actions/workflows/symfony.yml/badge.svg)
<a href="https://github.com/manuxi/SuluNewsBundle/blob/main/LICENSE" target="_blank">
<img src="https://img.shields.io/github/license/manuxi/SuluNewsBundle" alt="GitHub license">
</a>
<a href="https://github.com/manuxi/SuluNewsBundle/tags" target="_blank">
<img src="https://img.shields.io/github/v/tag/manuxi/SuluNewsBundle" alt="GitHub license">
</a>

I made this bundle to have the possibility to manage news in my projects without the dependency to elasticsearch.

This bundle contains
- Several filters for News Content Type
- Link Provider
- Sitemap Provider
- Handler for Trash Items
- Handler for Automation
- Possibility to assign a contact as author
- Twig Extension for resolving news / get a list of news
- Events for displaying Activities
- Search indexes 
  - refresh whenever entity is changed
  - distinct between normal and draft
and more...

The news and their meta information is translatable. 

It contains an example twig template. 

Please feel comfortable submitting feature requests. 
This bundle is still in development. Use at own risk 🤞🏻

![image](https://github.com/user-attachments/assets/eae6259e-01c8-4e80-9613-b186687701b9)

## 👩🏻‍🏭 Installation
Install the package with:
```console
composer require manuxi/sulu-news-bundle
```
If you're *not* using Symfony Flex, you'll also
need to add the bundle in your `config/bundles.php` file:

```php
return [
    //...
    Manuxi\SuluNewsBundle\SuluNewsBundle::class => ['all' => true],
];
```
Please add the following to your `routes_admin.yaml`:
```yaml
SuluNewsBundle:
    resource: '@SuluNewsBundle/Resources/config/routes_admin.yml'
```
Don't forget fo add the index to your sulu_search.yaml:

add "news"!

"news" is the index of published, "news_draft" the index of unpublished elements.
```yaml
sulu_search:
    website:
        indexes:
            - news
            - ...
``` 
Last but not least the schema of the database needs to be updated.  

Some tables will be created (prefixed with app_):  
news, news_translation, news_seo, news_excerpt
(plus some ManyToMany relation tables).  

See the needed queries with
```
php bin/console doctrine:schema:update --dump-sql
```  
Update the schema by executing 
```
php bin/console doctrine:schema:update --force
```  

Make sure you only process the bundles schema updates!

## 🎣 Usage
First: Grant permissions for news. 
After page reload you should see the news item in the navigation. 
Start to create news.
Use smart_content property type to show a list of news, e.g.:
```xml
<property name="newslist" type="smart_content">
    <meta>
        <title lang="en">News</title>
        <title lang="de">News</title>
    </meta>
    <params>
        <param name="provider" value="news"/>
        <param name="max_per_page" value="5"/>
        <param name="page_parameter" value="page"/>
    </params>
</property>
```
Example of the corresponding twig template for the news list:
```html
{% for news in newslist %}
    <div class="col">
        <h2>
            {{ news.title }}
        </h2>
        <h3>
            {{ news.subtitle }}
        </h3>
        <p>
            {{ news.created|format_datetime('full', 'none', locale=app.request.getLocale()) }}
        </p>
        <p>
            {{ news.summary|raw }}
        </p>
        <p>
            <a class="btn btn-primary" href="{{ news.routePath }}" role="button">
                {{ "Read more..."|trans }} <i class="fa fa-angle-double-right"></i>
            </a>
        </p>
    </div>
{% endfor %}
```

Since the seo and excerpt tabs are available in the news editor, 
meta information can be provided like it's done as usual when rendering your pages. 

## 👩‍🍳 Contributing
For the sake of simplicity this extension was kept small.
Please feel comfortable submitting issues or pull requests. As always I'd be glad to get your feedback to improve the extension :).
