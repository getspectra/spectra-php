<?php

namespace Overtrue\Spectra;

use Closure;
use Overtrue\Spectra\DataLoaders\DataLoaderInterface;
use Overtrue\Spectra\Debug\ExpressionDebugger;
use Overtrue\Spectra\Polices\PolicyInterface;

class Spectra
{
    public static function validate(array $polices, DataLoaderInterface|Closure $dataLoader, string $permissionName): bool
    {
        // Find all relevant policies
        $relevantPolices = self::getRelevantPolices($polices, $permissionName);

        // Parse all resources required from policies
        $fieldsToLoad = self::getRequiredFieldsFromPolicies($relevantPolices);

        // Load all necessary data
        $data = self::loadAllNecessaryData($dataLoader, $fieldsToLoad);

        // Bisect policies into DENY and ALLOW policies
        [$denyPolicies, $allowPolicies] = self::bisectPoliciesIntoDenyAndAllowPolicies($relevantPolices);

        // Return false if any of the DENY policies evaluate to true
        /** @var array<PolicyInterface> $denyPolicies */
        foreach ($denyPolicies as $denyPolicy) {
            if ($denyPolicy->apply($data)) {
                return false;
            }
        }

        // Return true if any of the ALLOW policies evaluate to true
        /** @var array<PolicyInterface> $allowPolicies */
        foreach ($allowPolicies as $allowPolicy) {
            if ($allowPolicy->apply($data)) {
                return true;
            }
        }

        return false;
    }

    public static function debug(array $polices, DataLoaderInterface|Closure $dataLoader, string $permissionName): array
    {
        $report = [
            'policies' => [],
            'permission' => $permissionName,
        ];

        foreach ($polices as $police) {
            assert($police instanceof PolicyInterface);

            $report['policies'][spl_object_id($police)] = [
                'class' => get_class($police),
                'description' => $police->getDescription(),
                'effect' => $police->getEffect(),
                'permissions' => $police->getPermissions(),
                'fields' => $police->getFields(),
                'applied' => false,
                'matched' => false,
                'expression' => null,
            ];
        }

        // Find all relevant policies
        $relevantPolices = self::getRelevantPolices($polices, $permissionName);

        // Parse all resources required from policies
        $report['fields'] = $fieldsToLoad = self::getRequiredFieldsFromPolicies($relevantPolices);

        // Load all necessary data
        $data = self::loadAllNecessaryData($dataLoader, $fieldsToLoad);

        // Bisect policies into DENY and ALLOW policies
        [$denyPolicies, $allowPolicies] = self::bisectPoliciesIntoDenyAndAllowPolicies($relevantPolices);

        // Return false if any of the DENY policies evaluate to true
        foreach ($denyPolicies as $denyPolicy) {
            $report['policies'][spl_object_id($denyPolicy)]['applied'] = true;
            $report['policies'][spl_object_id($denyPolicy)]['matched'] = $matched = $denyPolicy->apply($data);
            $report['policies'][spl_object_id($denyPolicy)]['filter'] = ExpressionDebugger::debug($denyPolicy->getFilter(), $data);

            if ($matched) {
                return $report;
            }
        }

        // Return true if any of the ALLOW policies evaluate to true
        foreach ($allowPolicies as $allowPolicy) {
            $report['policies'][spl_object_id($allowPolicy)]['applied'] = true;
            $report['policies'][spl_object_id($allowPolicy)]['matched'] = $matched = $allowPolicy->apply($data);
            $report['policies'][spl_object_id($allowPolicy)]['filter'] = ExpressionDebugger::debug($allowPolicy->getFilter(), $data);

            if ($matched) {
                return $report;
            }
        }

        return $report;
    }

    /**
     * @return array<\Overtrue\Spectra\Polices\PolicyInterface>
     */
    public static function getRelevantPolices(array $polices, string $permissionName): array
    {
        $relevantPolices = [];

        foreach ($polices as $policy) {
            if ($policy instanceof PolicyInterface && in_array($permissionName, $policy->getPermissions())) {
                $relevantPolices[] = $policy;
            }
        }

        return $relevantPolices;
    }

    /**
     * @param  array<PolicyInterface>  $polices
     * @return array<string>
     */
    public static function getRequiredFieldsFromPolicies(array $polices): array
    {
        $fieldsToLoad = [];

        foreach ($polices as $policy) {
            $fieldsToLoad = array_merge($fieldsToLoad, $policy->getFields());
        }

        return $fieldsToLoad;
    }

    /**
     * @param  array<PolicyInterface>  $polices
     * @return array[array<PolicyInterface>, array<PolicyInterface>]
     */
    public static function bisectPoliciesIntoDenyAndAllowPolicies(array $polices): array
    {
        $denyPolicies = [];
        $allowPolicies = [];

        foreach ($polices as $policy) {
            if ($policy->getEffect() === Effect::DENY) {
                $denyPolicies[] = $policy;
            } else {
                $allowPolicies[] = $policy;
            }
        }

        return [$denyPolicies, $allowPolicies];
    }

    /**
     * @return array<string, mixed>
     */
    public static function loadAllNecessaryData(DataLoaderInterface|Closure $dataLoader, array $fieldsToLoad): mixed
    {
        $data = $dataLoader instanceof Closure ? $dataLoader($fieldsToLoad) : $dataLoader->load($fieldsToLoad);

        if (! is_array($data) || array_is_list($data)) {
            throw new \InvalidArgumentException('Data must be an associative array.');
        }

        return $data;
    }
}
