<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\RecursiveProxyException;
use Spiral\Core\Internal\Introspector;
use Spiral\Core\Internal\Proxy;

/**
 * @internal
 */
final class Resolver
{
    public static function resolve(
        string $alias,
        \Stringable|string|null $context = null,
        ?ContainerInterface $c = null,
    ): object {
        $c ??= ContainerScope::getContainer() ?? throw new ContainerException('Proxy is out of scope.');

        try {
            /** @psalm-suppress TooManyArguments */
            $result = $c->get($alias, $context) ?? throw new ContainerException(
                'Resolved `null` from the container.',
            );
        } catch (\Throwable $e) {
            $scope = self::getScope($c);
            throw new ContainerException(
                $scope === null
                    ? "Unable to resolve `{$alias}` in a Proxy."
                    : \sprintf('Unable to resolve `%s` in a Proxy in `%s` scope.', $alias, \implode('.', $scope)),
                previous: $e,
            );
        }

        /**
         * If we get a Proxy again, that we should retry with the new context
         * to try to get the instance from the Proxy Fallback Factory.
         * If there is no the Proxy Fallback Factory, {@see RecursiveProxyException} will be thrown.
         */
        try {
            return Proxy::isProxy($result)
                ? $c->get($alias, new RetryContext($context))
                : $result;
        } catch (RecursiveProxyException $e) {
            $scope = self::getScope($c);
            throw new RecursiveProxyException($e->alias, $e->bindingScope, $scope);
        }
    }

    /**
     * @return non-empty-string|null
     */
    private static function getScope(ContainerInterface $c): ?array
    {
        if (!$c instanceof Container) {
            if (!Proxy::isProxy($c)) {
                return null;
            }

            $c = null;
        }

        return \array_reverse(\array_map(
            static fn (?string $name): string => $name ?? 'null',
            Introspector::scopeNames($c),
        ));
    }
}
