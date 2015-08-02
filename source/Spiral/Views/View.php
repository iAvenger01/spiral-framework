<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Debug\Traits\BenchmarkTrait;

/**
 * Default view implementation which can work with Compiler based classes.
 */
class View extends Component implements ViewInterface
{
    /**
     * For render benchmarking.
     */
    use BenchmarkTrait;

    /**
     * This is magick constant used by Spiral Container, it helps system to resolve controllable
     * injections.
     */
    const INJECTOR = ViewManager::class;

    /**
     * Must point to valid view path.
     */
    const VIEW = '';

    /**
     * @var CompilerInterface
     */
    protected $compiler = null;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(CompilerInterface $compiler, array $data = [])
    {
        $this->compiler = $compiler;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        //Benchmarking context
        $context = $this->compiler->getNamespace()
            . ViewsInterface::NS_SEPARATOR
            . $this->compiler->getView();

        $this->benchmark('render', $context);

        $outerBuffer = ob_get_level();

        ob_start();
        extract($this->data, EXTR_OVERWRITE);
        try
        {
            include $this->compiler->viewFilename();
        }
        catch (\Exception $exception)
        {
            while (ob_get_level() > $outerBuffer)
            {
                ob_end_clean();
            }

            throw $exception;
        }

        $result = ob_get_clean();
        $this->benchmark('render', $context);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    final public function __toString()
    {
        return $this->render();
    }
}