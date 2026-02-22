<?php

declare(strict_types=1);

namespace Symkit\BuilderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Component\Finder\Finder;

final class BlockSynchronizer
{
    /**
     * @param class-string $blockClass
     * @param class-string $blockCategoryClass
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $projectDir,
        private readonly string $blockClass,
        private readonly string $blockCategoryClass,
    ) {
    }

    public function sync(bool $includeSnippets = false): void
    {
        $categoryObjects = $this->syncCategories();
        $this->syncCoreBlocks($categoryObjects);

        if ($includeSnippets) {
            $this->syncSnippets($categoryObjects);
        }

        $this->entityManager->flush();
    }

    /**
     * @return array<string, object>
     */
    private function syncCategories(): array
    {
        $categories = [
            'text' => ['label' => 'Text', 'position' => 0],
            'media' => ['label' => 'Media', 'position' => 1],
            'layout' => ['label' => 'Layout & Design', 'position' => 2],
            'design' => ['label' => 'Visual Elements', 'position' => 3],
            'marketing' => ['label' => 'Marketing & Guides', 'position' => 4],
            'content' => ['label' => 'Content', 'position' => 5],
        ];

        $categoryObjects = [];
        foreach ($categories as $code => $catData) {
            $category = $this->entityManager->getRepository($this->blockCategoryClass)->findOneBy(['code' => $code]);
            if (!$category) {
                $category = new ($this->blockCategoryClass)();
                $category->setCode($code);
                $this->entityManager->persist($category);
            }

            $category->setLabel($catData['label']);
            $category->setPosition($catData['position']);
            $categoryObjects[$code] = $category;
        }

        return $categoryObjects;
    }

    /**
     * @param array<string, object> $categoryObjects
     */
    private function syncCoreBlocks(array $categoryObjects): void
    {
        $blocks = [
            'paragraph' => [
                'label' => 'Text',
                'category' => 'text',
                'icon' => 'heroicons:bars-3-bottom-left-20-solid',
                'defaultData' => ['content' => '', 'editMode' => 'visual'],
                'template' => '@SymkitBuilder/blocks/paragraph.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="prose dark:prose-invert max-w-none">
                        {{ data.content|raw }}
                    </div>
                    HTML,
            ],
            'image' => [
                'label' => 'Image',
                'category' => 'media',
                'icon' => 'heroicons:photo-20-solid',
                'defaultData' => ['mediaId' => null],
                'template' => '@SymkitBuilder/blocks/image.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="w-full my-8">
                        {% if data.url is defined and data.url %}
                            <img src="{{ data.url }}" alt="{{ data.alt|default('') }}" class="w-full h-auto rounded-lg shadow-sm" />
                        {% endif %}
                    </div>
                    HTML,
            ],
            'quote' => [
                'label' => 'Quote',
                'category' => 'text',
                'icon' => 'heroicons:chat-bubble-bottom-center-text-20-solid',
                'defaultData' => ['content' => '', 'author' => '', 'editMode' => 'visual'],
                'template' => '@SymkitBuilder/blocks/quote.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="relative pl-6 border-l-4 border-indigo-500 my-8">
                        <div class="italic text-xl text-gray-700 dark:text-gray-300">
                            {{ data.content|raw }}
                        </div>
                        {% if data.author is not empty %}
                            <div class="mt-4 flex items-center gap-2 text-sm font-medium text-gray-500">
                                <span class="text-indigo-500">—</span>
                                <div>{{ data.author|raw }}</div>
                            </div>
                        {% endif %}
                    </div>
                    HTML,
            ],
            'table' => [
                'label' => 'Table',
                'category' => 'layout',
                'icon' => 'heroicons:table-cells-20-solid',
                'defaultData' => [
                    'rows' => [
                        ['cells' => [['content' => ''], ['content' => '']]],
                        ['cells' => [['content' => ''], ['content' => '']]],
                    ],
                    'hasHeader' => true,
                ],
                'template' => '@SymkitBuilder/blocks/table.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="not-prose overflow-hidden bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-200 dark:ring-white/10 rounded-xl my-8">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                    {% for rIndex, row in data.rows %}
                                        <tr class="{{ data.hasHeader and loop.first ? 'bg-gray-50 dark:bg-white/5' : 'hover:bg-gray-50/50 dark:hover:bg-white/[0.02]' }} transition-colors">
                                            {% for cIndex, cell in row.cells %}
                                                <td class="px-6 py-4 text-sm border-r border-gray-100 dark:border-white/5 last:border-r-0">
                                                    <div class="{{ data.hasHeader and loop.parent.loop.first ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300' }}">
                                                        {{ cell.content|raw }}
                                                    </div>
                                                </td>
                                            {% endfor %}
                                        </tr>
                                    {% endfor %}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    HTML,
            ],
            'list' => [
                'label' => 'List',
                'category' => 'content',
                'icon' => 'heroicons:list-bullet-20-solid',
                'defaultData' => ['items' => [['content' => '']], 'type' => 'ul', 'style' => 'default', 'editMode' => 'visual'],
                'template' => '@SymkitBuilder/blocks/list.html.twig',
                'htmlCode' => <<<'HTML'
                    <{{ data.type }} class="space-y-1 relative {{ data.type == 'ul' ? 'list-disc pl-5' : 'list-decimal pl-5' }} my-6 text-gray-600 dark:text-gray-300">
                        {% for item in data.items %}
                            <li class="group/item relative gap-3">
                                <span class="flex-auto py-1">{{ item.content|raw }}</span>
                            </li>
                        {% endfor %}
                    </{{ data.type }}>
                    HTML,
            ],
            'code' => [
                'label' => 'Code',
                'category' => 'content',
                'icon' => 'heroicons:code-bracket-20-solid',
                'defaultData' => ['code' => '', 'language' => 'javascript'],
                'template' => '@SymkitBuilder/blocks/code.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="not-prose relative bg-gray-900 rounded-xl overflow-hidden ring-1 ring-white/10 my-8 shadow-lg">
                        <div class="flex items-center justify-between px-4 py-2 bg-gray-800/50 border-b border-white/5">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/20"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/20"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/20"></div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ data.language }}</span>
                        </div>
                        <pre class="p-6 text-sm text-indigo-100/90 leading-relaxed overflow-x-auto"><code class="language-{{ data.language }}">{{ data.code|raw }}</code></pre>
                    </div>
                    HTML,
            ],
            'infobox' => [
                'label' => 'Info Box',
                'category' => 'design',
                'icon' => 'heroicons:information-circle-20-solid',
                'defaultData' => ['content' => '', 'type' => 'info'],
                'template' => '@SymkitBuilder/blocks/infobox.html.twig',
                'htmlCode' => <<<'HTML'
                    {% set types = {
                        'info': { 'bg': 'bg-blue-50 dark:bg-blue-500/10', 'border': 'border-blue-200 dark:border-blue-500/20', 'text': 'text-blue-800 dark:text-blue-200', 'icon': 'heroicons:information-circle-20-solid', 'accent': 'bg-blue-500' },
                        'success': { 'bg': 'bg-green-50 dark:bg-green-500/10', 'border': 'border-green-200 dark:border-green-500/20', 'text': 'text-green-800 dark:text-green-200', 'icon': 'heroicons:check-circle-20-solid', 'accent': 'bg-green-500' },
                        'warning': { 'bg': 'bg-yellow-50 dark:bg-yellow-500/10', 'border': 'border-yellow-200 dark:border-yellow-500/20', 'text': 'text-yellow-800 dark:text-yellow-200', 'icon': 'heroicons:exclamation-triangle-20-solid', 'accent': 'bg-yellow-500' },
                        'error': { 'bg': 'bg-red-50 dark:bg-red-500/10', 'border': 'border-red-200 dark:border-red-500/20', 'text': 'text-red-800 dark:text-red-200', 'icon': 'heroicons:x-circle-20-solid', 'accent': 'bg-red-500' }
                    } %}
                    {% set current = types[data.type|default('info')] %}
                    <div class="relative overflow-hidden rounded-xl border {{ current.border }} {{ current.bg }} p-4 pl-12 my-6">
                        <div class="absolute top-4 left-4 text-blue-500">
                            {{ ux_icon(current.icon, {class: 'w-5 h-5 ' ~ current.text|replace({'text-': 'text-'})}) }}
                        </div>
                        <div class="absolute top-0 left-0 bottom-0 w-1 {{ current.accent }}"></div>
                        <div class="text-sm leading-relaxed {{ current.text }}">
                            {{ data.content|raw }}
                        </div>
                    </div>
                    HTML,
            ],
            'cta' => [
                'label' => 'CTA',
                'category' => 'marketing',
                'icon' => 'heroicons:cursor-arrow-rays-20-solid',
                'defaultData' => ['text' => '', 'buttonText' => 'Click Here', 'url' => '', 'style' => 'primary'],
                'template' => '@SymkitBuilder/blocks/cta.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="not-prose flex flex-col items-center text-center p-12 bg-indigo-50 dark:bg-indigo-500/5 rounded-3xl border-2 border-dashed border-indigo-100 dark:border-indigo-500/20 my-12">
                        {% if data.text is not empty %}
                            <div class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ data.text|raw }}</div>
                        {% endif %}
                        <a href="{{ data.url }}" 
                           class="inline-flex items-center justify-center px-8 py-3 rounded-full text-sm font-bold shadow-sm transition-all {{ data.style|default('primary') == 'primary' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50' }}">
                            {{ data.buttonText|raw }}
                        </a>
                    </div>
                    HTML,
            ],
            'howto' => [
                'label' => 'How To',
                'category' => 'marketing',
                'icon' => 'heroicons:academic-cap-20-solid',
                'defaultData' => [
                    'steps' => [['title' => '', 'content' => '']],
                ],
                'template' => '@SymkitBuilder/blocks/howto.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="p-8 bg-white dark:bg-gray-900 rounded-3xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm my-8">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                            <span class="flex-none w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center">
                                {{ ux_icon('heroicons:academic-cap-20-solid', {class: 'w-5 h-5'}) }}
                            </span>
                            How To Guide
                        </h3>
                        <div class="space-y-8">
                            {% for i, step in data.steps %}
                                <div class="group/step relative flex gap-6">
                                    <div class="flex-none flex flex-col items-center">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border-2 border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-black flex items-center justify-center text-sm shadow-sm ring-4 ring-white dark:ring-gray-900 z-10">{{ loop.index }}</div>
                                        {% if not loop.last %}
                                            <div class="w-px flex-auto bg-gray-100 dark:bg-white/5 my-2"></div>
                                        {% endif %}
                                    </div>
                                    <div class="pb-8 flex-auto">
                                        <div class="text-base font-bold text-gray-900 dark:text-white mb-2">{{ step.title|raw }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ step.content|raw }}</div>
                                    </div>
                                </div>
                            {% endfor %}
                        </div>
                    </div>
                    HTML,
            ],
            'separator' => [
                'label' => 'Separator',
                'category' => 'layout',
                'icon' => 'heroicons:minus-20-solid',
                'defaultData' => ['style' => 'solid'],
                'template' => '@SymkitBuilder/blocks/separator.html.twig',
                'htmlCode' => <<<'HTML'
                    <hr class="border-t-2 {{ data.style|default('solid') == 'solid' ? 'border-solid' : (data.style == 'dashed' ? 'border-dashed' : 'border-dotted') }} border-gray-200 dark:border-white/10 my-8">
                    HTML,
            ],
            'video' => [
                'label' => 'Video',
                'category' => 'media',
                'icon' => 'heroicons:video-camera-20-solid',
                'defaultData' => ['url' => '', 'provider' => 'youtube'],
                'template' => '@SymkitBuilder/blocks/video.html.twig',
                'htmlCode' => <<<'HTML'
                    <div class="aspect-video bg-gray-900 rounded-2xl overflow-hidden ring-1 ring-white/10 my-8 shadow-lg">
                        {% if data.embedUrl is defined and data.embedUrl %}
                            <iframe class="w-full h-full" src="{{ data.embedUrl }}" frameborder="0" allowfullscreen></iframe>
                        {% else %}
                            <div class="flex items-center justify-center h-full text-gray-400">
                                {{ ux_icon('heroicons:video-camera-slash-20-solid', {class: 'w-12 h-12 opacity-50'}) }}
                            </div>
                        {% endif %}
                    </div>
                    HTML,
            ],
            'faq_block' => [
                'label' => 'FAQ Block',
                'category' => 'marketing',
                'icon' => 'heroicons:question-mark-circle-20-solid',
                'defaultData' => ['faqCode' => ''],
                'template' => '@SymkitBuilder/blocks/faq_block.html.twig',
            ],
        ];

        foreach ($blocks as $code => $data) {
            $this->upsertBlock($code, $data, $categoryObjects);
        }
    }

    /**
     * @param array<string, object> $categoryObjects
     */
    private function syncSnippets(array &$categoryObjects): void
    {
        foreach ($this->discoverSnippets() as $snippet) {
            $catCode = $snippet['category'];
            if (!isset($categoryObjects[$catCode])) {
                $category = $this->entityManager->getRepository($this->blockCategoryClass)->findOneBy(['code' => $catCode]);
                if (!$category) {
                    $category = new ($this->blockCategoryClass)();
                    $category->setCode($catCode);
                    $this->entityManager->persist($category);
                }
                $category->setLabel(ucwords(str_replace(['_', '-'], ' ', $catCode)));
                $category->setPosition(10);
                $categoryObjects[$catCode] = $category;
            }

            $this->upsertBlock($snippet['code'], $snippet['data'], $categoryObjects);
        }
    }

    private function discoverSnippets(): Generator
    {
        $snippetDir = $this->projectDir.'/packages/builder-bundle/resources/data/snippets/tailwind';
        if (!is_dir($snippetDir)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($snippetDir)->name('*.json');

        foreach ($finder as $file) {
            $data = json_decode($file->getContents(), true, 512, \JSON_THROW_ON_ERROR);
            $code = 'tw_'.str_replace(['/', '\\'], '_', $file->getRelativePathname());
            $code = str_replace('.json', '', $code);

            $catCode = $file->getRelativePath() ?: 'tailwind';

            yield [
                'code' => $code,
                'category' => $catCode,
                'data' => [
                    'label' => $data['label'],
                    'category' => $catCode,
                    'icon' => $data['icon'] ?? 'heroicons:sparkles-20-solid',
                    'defaultData' => [
                        'html' => '<div class="not-prose">'.$data['html'].'</div>',
                        'editMode' => 'visual',
                    ],
                    'template' => '@SymkitBuilder/blocks/snippet.html.twig',
                ],
            ];
        }
    }

    /**
     * @param array<string, object> $categoryObjects
     */
    private function upsertBlock(string $code, array $data, array $categoryObjects): void
    {
        $block = $this->entityManager->getRepository($this->blockClass)->findOneBy(['code' => $code]);
        if (!$block) {
            $block = new ($this->blockClass)();
            $block->setCode($code);
            $this->entityManager->persist($block);
        }

        $block->setLabel((string) $data['label']);
        $block->setCategory($categoryObjects[$data['category']] ?? $categoryObjects['text']);
        $block->setIcon($data['icon']);
        $block->setTemplate($data['template'] ?? null);
        $block->setHtmlCode($data['htmlCode'] ?? null);
        $block->setDefaultData($data['defaultData']);
        $block->setIsActive(true);
    }
}
