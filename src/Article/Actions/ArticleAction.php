<?php

declare(strict_types=1);

namespace App\Article\Actions;

use Framework\Database\Table\ArticleTable;
use Framework\Database\Table\CategorieTable;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

final readonly class ArticleAction
{
    public function __construct(
        private RendererInterface $renderer,
        private ArticleTable $articleTable,
        private CategorieTable $categorieTable,
        private ContainerInterface $container
    ) {
    }

    public function __invoke(Request $request): string
    {
        if ($request->getAttribute('id')) {
            return $this->show($request);
        }
        return $this->index();
    }

    /**
     * Display recents articles
     *
     */
    private function index(): string
    {
        $articles = $this->articleTable->findAll("created_at DESC", 12);
        return $this->renderer->render('@article/index', ['articles' => $articles]);
    }

    /**
     * display one article
     *
     */
    private function show(Request $request): string
    {
        $article = $this->articleTable->find((int)$request->getAttribute('id'));
        $id = $article->__get('id');
        $categorie = $this->categorieTable->findFromTable('article', $id);
        $paymentActive = $this->container->has('pay.prefix');
        return $this->renderer->render(
            '@article/show',
            ['article' => $article, 'categorie' => $categorie, 'paymentActive' => $paymentActive]
        );
    }
}
