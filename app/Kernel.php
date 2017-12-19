<?php

namespace App;

use Phpml\Regression\LeastSquares;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @var \Phpml\Regression\LeastSquares
     */
    public $regression;

    /**
     * Kernel constructor.
     *
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        $this->regression = new LeastSquares();
    }

    /**
     * @return array|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
        ];
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function train(Request $request){

        if(!$request->query->has('title') || !$request->query->has('click')){
            return new JsonResponse([
                'title' => 'string',
                'click' => 'integer|>0',
            ]);
        }

        $train = (new Title(
            $request->query->get('title'),
            $request->query->get('click')
        ))->get();

        $this->regression->train($train['samples'], $train['targets']);

        return new JsonResponse();
    }


    /**
     * @param string $name
     *
     * @return JsonResponse
     */
    public function predict(string $name){

        $predict =  (new Title($name))->get();

        $variants = $this->regression->predict($predict['samples']);
        $average = array_sum($variants) / count($variants);

        return new JsonResponse([
            'average' => $average,
            'intercept' => $this->regression->getIntercept(),
            'coefficients' => $this->regression->getCoefficients(),
        ]);
    }


    /**
     * standard Symfony cache directory
     *
     * @return string
     */
    public function getCacheDir()
    {
        return __DIR__ . '/../storage/cache/' . $this->getEnvironment();
    }

    /**
     * standard Symfony logs directory
     *
     * @return string
     */
    public function getLogDir()
    {
        return __DIR__ . '/../storage/log/' . $this->getEnvironment();
    }

    /**
     * @param ContainerBuilder $c
     * @param LoaderInterface  $loader
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET',
        ]);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        // kernel is a service that points to this class
        // optional 3rd argument is the route name
        $routes->add('/train', 'kernel:train');
        $routes->add('/predict/{name}', 'kernel:predict');
    }
}
