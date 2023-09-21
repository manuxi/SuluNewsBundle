<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Service;

use Sulu\Bundle\MediaBundle\Content\Types\CollectionSelection;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsTypeSelect
{

    private TranslatorInterface $translator;
    private array $typesMap = [
        'default'       => 'sulu_news.type.default',
        'article'       => 'sulu_news.type.article',
        'blog'          => 'sulu_news.type.blog',
        'faq'           => 'sulu_news.type.faq',
        'notice'        => 'sulu_news.type.notice',
        'announcement'  => 'sulu_news.type.announcement',
        'rating'        => 'sulu_news.type.rating',
    ];
    private string $defaultValue = 'article';

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getValues(): array
    {
        $values = [];

        foreach ($this->typesMap as $code => $toTrans) {
            $values[] = [
                'name' => $code,
                'title' => $this->translator->trans($toTrans),
            ];
        }

        return $values;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}