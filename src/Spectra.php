<?php

namespace Overtrue\Spectra;

use Overtrue\Spectra\DataLoaders\DataLoaderInterface;
use Overtrue\Spectra\Debug\ExpressDebugger;
use Overtrue\Spectra\Polices\PolicyInterface;

class Spectra
{
    public static function validate(array $polices, DataLoaderInterface $dataLoader, string $permissionName): bool
    {
        // Find all relevant policies
        $relevantPolices = self::getRelevantPolices($polices, $permissionName);

        // Parse all resources required from policies
        $fieldsToLoad = self::getRequiredFieldsFromPolicies($relevantPolices);

        // Load all necessary data
        $data = $dataLoader->load($fieldsToLoad);

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

    public static function debug(array $polices, DataLoaderInterface $dataLoader, string $permissionName): array
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
        $report['data'] = $data = $dataLoader->load($fieldsToLoad);

        // Bisect policies into DENY and ALLOW policies
        [$denyPolicies, $allowPolicies] = self::bisectPoliciesIntoDenyAndAllowPolicies($relevantPolices);

        // Return false if any of the DENY policies evaluate to true
        foreach ($denyPolicies as $denyPolicy) {
            $report['policies'][spl_object_id($denyPolicy)]['applied'] = true;
            $report['policies'][spl_object_id($denyPolicy)]['matched'] = $matched = $denyPolicy->apply($data);
            $report['policies'][spl_object_id($denyPolicy)]['expression'] = ExpressDebugger::debug($denyPolicy->getApplyFilter(), $data);

            if ($matched) {
                return $report;
            }
        }

        // Return true if any of the ALLOW policies evaluate to true
        foreach ($allowPolicies as $allowPolicy) {
            $report['policies'][spl_object_id($allowPolicy)]['applied'] = true;
            $report['policies'][spl_object_id($allowPolicy)]['matched'] = $matched = $allowPolicy->apply($data);
            $report['policies'][spl_object_id($allowPolicy)]['expression'] = ExpressDebugger::debug($allowPolicy->getApplyFilter(), $data);

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
}
