<?php

declare(strict_types=1);

namespace DVC\ContaoCircles\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

final class CircleElementDca
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsCallback(table: 'tl_content', target: 'config.onload', priority: -255)]
    public function configureCircleDataField(DataContainer $dc): void
    {
        if (!$this->isCircleElement($dc)) {
            return;
        }

        $GLOBALS['TL_CSS'][] = 'bundles/contaocircles/css/contao-circles-be.css|static';

        $GLOBALS['TL_DCA']['tl_content']['fields']['rsce_data'] = [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['circle_items'],
            'exclude' => false,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'tl_class' => 'clr',
                'minCount' => 3,
                'maxCount' => 3,
                'hideButtons' => true,
                'columnFields' => [
                    'linkText' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_content']['circle_link_text'],
                        'inputType' => 'text',
                        'eval' => [
                            'mandatory' => true,
                            'maxlength' => 128,
                            'tl_class' => 'clr',
                        ],
                    ],
                    'content' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_content']['circle_content'],
                        'inputType' => 'textarea',
                        'eval' => [
                            'mandatory' => true,
                            'allowHtml' => true,
                            'rte' => 'tinyMCE',
                            'style' => 'height:120px',
                            'tl_class' => 'clr',
                        ],
                    ],
                ],
            ],
            'sql' => 'mediumblob NULL',
        ];
    }

    private function isCircleElement(DataContainer $dc): bool
    {
        if ($dc->activeRecord && 'contao_circles' === ($dc->activeRecord->type ?? null)) {
            return true;
        }

        $id = $dc->id ?: $this->requestStack->getCurrentRequest()?->query->get('id');

        if (!is_numeric($id)) {
            return false;
        }

        return 'contao_circles' === $this->connection->fetchOne(
            'SELECT type FROM tl_content WHERE id = ?',
            [(int) $id],
        );
    }
}
