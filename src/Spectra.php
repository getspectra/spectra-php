<?php

namespace Overtrue\Spectra;

use Overtrue\Spectra\DataLoaders\DataLoaderInterface;
use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\ExpressionInterface;
use Overtrue\Spectra\Expressions\NotExpression;
use Overtrue\Spectra\Expressions\OrExpression;
use Overtrue\Spectra\Polices\PolicyInterface;

class Spectra
{
    public static function validate(array $polices, DataLoaderInterface $dataLoader, string $permissionName): bool
    {
        // Find all relevant policies
        /** @var array<\Overtrue\Spectra\Polices\PolicyInterface> $relevantPolices */
        $relevantPolices = [];
        foreach ($polices as $policy) {
            if (in_array($permissionName, $policy->getPermissions())) {
                $relevantPolices[] = $policy;
            }
        }

        // Parse all resources required from policies
        $fieldsToLoad = [];
        foreach ($relevantPolices as $policy) {
            $fieldsToLoad = array_merge($fieldsToLoad, $policy->getFields());
        }

        // Load all necessary data
        $data = $dataLoader->load($fieldsToLoad);

        // Bisect policies into DENY and ALLOW policies
        $denyPolicies = [];
        $allowPolicies = [];
        foreach ($relevantPolices as $policy) {
            if ($policy->getEffect() === Effect::DENY) {
                $denyPolicies[] = $policy;
            } else {
                $allowPolicies[] = $policy;
            }
        }

        // Return false if any of the DENY policies evaluate to true
        foreach ($denyPolicies as $denyPolicy) {
            if (self::match($denyPolicy, $data)) {
                return false;
            }
        }

        // Return true if any of the ALLOW policies evaluate to true
        foreach ($allowPolicies as $allowPolicy) {
            if (self::match($allowPolicy, $data)) {
                return true;
            }
        }

        return false;
    }

    public static function parseExpression(string|array $definition): ExpressionInterface
    {
        if (is_string($definition)) {
            $definition = json_decode($definition, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid policy definition');
            }
        }

        // ['field', '=', 'value']
        if (array_is_list($definition)) {
            if (count($definition) !== 3 || Operation::tryFrom($definition[1]) === null) {
                throw new \InvalidArgumentException('Invalid policy definition');
            }

            return new BinaryExpression($definition[0], $definition[1], $definition[2]);
        }

        // {'and': [['field1', '=', 'value'], ['field2', '=', 'value'], ...]}
        if (array_key_exists('and', $definition)) {
            return new AndExpression(array_map(__METHOD__, $definition['and']));
        }

        // {'or': [['field1', '=', 'value'], ['field2', '=', 'value'], ...]}
        if (array_key_exists('or', $definition)) {
            return new OrExpression(array_map(__METHOD__, $definition['or']));
        }

        // {'not': ['field1', '=', 'value']}
        if (array_key_exists('not', $definition)) {
            return new NotExpression(self::parseExpression($definition['not']));
        }

        throw new \InvalidArgumentException('Invalid policy definition');
    }

    public static function match(PolicyInterface $policy, array $data)
    {
        return $policy->getApplyFilter()->evaluate($data);
    }
}
