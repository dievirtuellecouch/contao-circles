<?php

declare(strict_types=1);

namespace DVC\ContaoCircles\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('contao_circles', category: 'texts', template: 'ce_contao_circles')]
final class CirclesController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $items = $this->normalizeItems($model->rsce_data);

        if ([] === $items) {
            return new Response();
        }

        $GLOBALS['TL_CSS'][] = 'bundles/contaocircles/css/contao-circles.css|static';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocircles/js/contao-circles.min.js|static';

        $template->circles = $items;

        return $template->getResponse();
    }

    /**
     * @return list<array{key: string, linkText: string, content: string}>
     */
    private function normalizeItems(mixed $rawData): array
    {
        $data = $this->decodeData($rawData);
        $rows = $data['boxes'] ?? $data;

        if (!\is_array($rows)) {
            return [];
        }

        $items = [];

        foreach (array_values($rows) as $index => $row) {
            if (!\is_array($row)) {
                continue;
            }

            $linkText = trim((string) ($row['linkText'] ?? $row['name'] ?? ''));
            $content = trim((string) ($row['content'] ?? $row['information'] ?? ''));

            if ('' === $content && isset($row['text'])) {
                $content = trim((string) $row['text']);
            }

            if (isset($row['headline']) && '' !== trim((string) $row['headline'])) {
                $content = sprintf(
                    '<h2>%s</h2>%s',
                    StringUtil::specialchars((string) $row['headline']),
                    $content
                );
            }

            if ('' === $linkText || '' === $content) {
                continue;
            }

            $items[] = [
                'key' => 'circle'.($index + 1),
                'linkText' => $linkText,
                'content' => $content,
            ];
        }

        return $items;
    }

    private function decodeData(mixed $rawData): array
    {
        if (!\is_string($rawData) || '' === trim($rawData)) {
            return [];
        }

        $trimmed = trim($rawData);

        if ('{' === $trimmed[0] || '[' === $trimmed[0]) {
            $decoded = json_decode($trimmed, true);

            return \is_array($decoded) ? $decoded : [];
        }

        $decoded = StringUtil::deserialize($rawData, true);

        return \is_array($decoded) ? $decoded : [];
    }
}
