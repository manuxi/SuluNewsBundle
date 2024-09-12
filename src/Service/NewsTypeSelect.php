<?php

declare(strict_types=1);

namespace Manuxi\SuluNewsBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class NewsTypeSelect
{

    private array $typesMap = [
        'default'       => 'sulu_news.types.default',
        'article'       => 'sulu_news.types.article',
        'blog'          => 'sulu_news.types.blog',
        'faq'           => 'sulu_news.types.faq',
        'notice'        => 'sulu_news.types.notice',
        'announcement'  => 'sulu_news.types.announcement',
        'rating'        => 'sulu_news.types.rating',
    ];
    private string $defaultValue = 'default';

    public function __construct(private TranslatorInterface $translator)
    {}

    public function getValues(): array
    {
        $values = [];

        foreach ($this->typesMap as $code => $toTrans) {
            $values[] = [
                'name' => $code,
                'title' => $this->translator->trans($toTrans, [], 'admin'),
            ];
        }

        return $values;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}