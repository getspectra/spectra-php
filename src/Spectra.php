<?php

namespace Overtrue\Spectra;

use Overtrue\Spectra\DataLoaders\DataLoaderInterface;
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

    public static function match(PolicyInterface $policy, array $data)
    {
        return $policy->getApplyFilter()->evaluate($data);
    }
}