<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202112087\Symfony\Component\Console\Formatter;

use ConfigTransformer202112087\Symfony\Component\Console\Exception\InvalidArgumentException;
use ConfigTransformer202112087\Symfony\Contracts\Service\ResetInterface;
/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class OutputFormatterStyleStack implements \ConfigTransformer202112087\Symfony\Contracts\Service\ResetInterface
{
    /**
     * @var OutputFormatterStyleInterface[]
     */
    private $styles = [];
    /**
     * @var \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
     */
    private $emptyStyle;
    public function __construct(\ConfigTransformer202112087\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $emptyStyle = null)
    {
        $this->emptyStyle = $emptyStyle ?? new \ConfigTransformer202112087\Symfony\Component\Console\Formatter\OutputFormatterStyle();
        $this->reset();
    }
    /**
     * Resets stack (ie. empty internal arrays).
     */
    public function reset()
    {
        $this->styles = [];
    }
    /**
     * Pushes a style in the stack.
     * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style
     */
    public function push($style)
    {
        $this->styles[] = $style;
    }
    /**
     * Pops a style from the stack.
     *
     * @throws InvalidArgumentException When style tags incorrectly nested
     * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface|null $style
     */
    public function pop($style = null) : \ConfigTransformer202112087\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;
        }
        if (null === $style) {
            return \array_pop($this->styles);
        }
        foreach (\array_reverse($this->styles, \true) as $index => $stackedStyle) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);
                return $stackedStyle;
            }
        }
        throw new \ConfigTransformer202112087\Symfony\Component\Console\Exception\InvalidArgumentException('Incorrectly nested style tag found.');
    }
    /**
     * Computes current style with stacks top codes.
     */
    public function getCurrent() : \ConfigTransformer202112087\Symfony\Component\Console\Formatter\OutputFormatterStyle
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;
        }
        return $this->styles[\count($this->styles) - 1];
    }
    /**
     * @return $this
     * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $emptyStyle
     */
    public function setEmptyStyle($emptyStyle)
    {
        $this->emptyStyle = $emptyStyle;
        return $this;
    }
    public function getEmptyStyle() : \ConfigTransformer202112087\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
    {
        return $this->emptyStyle;
    }
}
