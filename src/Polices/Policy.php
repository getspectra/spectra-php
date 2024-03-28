<?php

namespace Overtrue\Spectra\Polices;

use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\ExpressionInterface;
use Overtrue\Spectra\Expressions\Factory as ExpressionFactory;
use Overtrue\Spectra\Utils;

class Policy implements \JsonSerializable, \Stringable, PolicyInterface
{
    public function __construct(
        public ExpressionInterface $expression,
        public string|Effect $effect = Effect::ALLOW,
        public array $permissions = [],
        public string $description = '',
    ) {
        if (is_string($this->effect)) {
            $this->effect = Effect::from(strtolower($this->effect));
        }
    }

    public static function parse(string|array $definition): self
    {
        if (is_string($definition)) {
            $definition = json_decode($definition, true);

            if ($definition === null) {
                throw new \InvalidArgumentException('Invalid JSON definition');
            }
        }

        if (empty($definition['apply_filter'])) {
            throw new \InvalidArgumentException('Missing apply_filter');
        }

        $effect = Effect::from(strtolower($definition['effect']));
        $expression = ExpressionFactory::parse($definition['apply_filter']);

        return new self($expression, $effect, $definition['permissions'] ?? [], $definition['description'] ?? '');
    }

    public function getDescription(): string
    {
        return $this->description ?? Utils::getBasename(get_class($this));
    }

    public function getFields(): array
    {
        return $this->getApplyFilter()->getFields();
    }

    public function getPermissions(): array
    {
        if (property_exists($this, 'permissions')) {
            return $this->permissions;
        }

        return [];
    }

    public function getEffect(): Effect
    {
        return $this->effect ?? Effect::ALLOW;
    }

    public function getApplyFilter(): ExpressionInterface
    {
        return $this->expression;
    }

    public function apply(array $data): bool
    {
        return $this->getApplyFilter()->evaluate($data);
    }

    public function jsonSerialize(): array
    {
        return [
            'description' => $this->getDescription(),
            'effect' => $this->getEffect()->value,
            'permissions' => $this->getPermissions(),
            'apply_filter' => $this->getApplyFilter(),
            'fields' => $this->getFields(),
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize());
    }
}
