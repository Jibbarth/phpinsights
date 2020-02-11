<?php


namespace NunoMaduro\PhpInsights\Domain\Composer;


final class ComposerValidator
{
    const COMPOSER_SCHEMA_PATH = "https://getcomposer.org/schema.json";

    /**
     * Validates the config, and returns the result.
     *
     * @return array
     */
    public function validate(string $file)
    {
        $arrayLoaderValidationFlags = 3; // check all
        $errors = [];
        $composer = Composer::fromPath($file);
        $manifest = $composer->getManifest();
        // validate json schema
        try {
            $this->validateSchema($manifest);
        /*} catch (JsonValidationException $e) {
            foreach ($e->getErrors() as $message) {
                    $errors[] = $message;
                }
            }*/
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();

            return $errors;
        }

        // validate actual data
        if (empty($manifest['license'])) {
            $warnings[] = 'No license specified, it is recommended to do so. For closed-source software you may use "proprietary" as license.';
        } else {
            $licenses = (array) $manifest['license'];

            // strip proprietary since it's not a valid SPDX identifier, but is accepted by composer
            foreach ($licenses as $key => $license) {
                if ('proprietary' === $license) {
                    unset($licenses[$key]);
                }
            }

            $licenseValidator = new SpdxLicenses();
            foreach ($licenses as $license) {
                $spdxLicense = $licenseValidator->getLicenseByIdentifier($license);
                if ($spdxLicense && $spdxLicense[3]) {
                    if (preg_match('{^[AL]?GPL-[123](\.[01])?\+$}i', $license)) {
                        $warnings[] = sprintf(
                            'License "%s" is a deprecated SPDX license identifier, use "'.str_replace('+', '', $license).'-or-later" instead',
                            $license
                        );
                    } elseif (preg_match('{^[AL]?GPL-[123](\.[01])?$}i', $license)) {
                        $warnings[] = sprintf(
                            'License "%s" is a deprecated SPDX license identifier, use "'.$license.'-only" or "'.$license.'-or-later" instead',
                            $license
                        );
                    } else {
                        $warnings[] = sprintf(
                            'License "%s" is a deprecated SPDX license identifier, see https://spdx.org/licenses/',
                            $license
                        );
                    }
                }
            }
        }

        if (isset($manifest['version'])) {
            $warnings[] = 'The version field is present, it is recommended to leave it out if the package is published on Packagist.';
        }

        if (!empty($manifest['name']) && preg_match('{[A-Z]}', $manifest['name'])) {
            $suggestName = preg_replace('{(?:([a-z])([A-Z])|([A-Z])([A-Z][a-z]))}', '\\1\\3-\\2\\4', $manifest['name']);
            $suggestName = strtolower($suggestName);

            $publishErrors[] = sprintf(
                'Name "%s" does not match the best practice (e.g. lower-cased/with-dashes). We suggest using "%s" instead. As such you will not be able to submit it to Packagist.',
                $manifest['name'],
                $suggestName
            );
        }

        if (!empty($manifest['type']) && $manifest['type'] == 'composer-installer') {
            $warnings[] = "The package type 'composer-installer' is deprecated. Please distribute your custom installers as plugins from now on. See https://getcomposer.org/doc/articles/plugins.md for plugin documentation.";
        }

        // check for require-dev overrides
        if (isset($manifest['require']) && isset($manifest['require-dev'])) {
            $requireOverrides = array_intersect_key($manifest['require'], $manifest['require-dev']);

            if (!empty($requireOverrides)) {
                $plural = (count($requireOverrides) > 1) ? 'are' : 'is';
                $warnings[] = implode(', ', array_keys($requireOverrides)). " {$plural} required both in require and require-dev, this can lead to unexpected behavior";
            }
        }

        // check for commit references
        $require = isset($manifest['require']) ? $manifest['require'] : array();
        $requireDev = isset($manifest['require-dev']) ? $manifest['require-dev'] : array();
        $packages = array_merge($require, $requireDev);
        foreach ($packages as $package => $version) {
            if (preg_match('/#/', $version) === 1) {
                $warnings[] = sprintf(
                    'The package "%s" is pointing to a commit-ref, this is bad practice and can cause unforeseen issues.',
                    $package
                );
            }
        }

        // report scripts-descriptions for non-existent scripts
        $scriptsDescriptions = isset($manifest['scripts-descriptions']) ? $manifest['scripts-descriptions'] : array();
        $scripts = isset($manifest['scripts']) ? $manifest['scripts'] : array();
        foreach ($scriptsDescriptions as $scriptName => $scriptDescription) {
            if (!array_key_exists($scriptName, $scripts)) {
                $warnings[] = sprintf(
                    'Description for non-existent script "%s" found in "scripts-descriptions"',
                    $scriptName
                );
            }
        }

        // check for empty psr-0/psr-4 namespace prefixes
        if (isset($manifest['autoload']['psr-0'][''])) {
            $warnings[] = "Defining autoload.psr-0 with an empty namespace prefix is a bad idea for performance";
        }
        if (isset($manifest['autoload']['psr-4'][''])) {
            $warnings[] = "Defining autoload.psr-4 with an empty namespace prefix is a bad idea for performance";
        }

        try {
            $loader = new ValidatingArrayLoader(new ArrayLoader(), true, null, $arrayLoaderValidationFlags);
            if (!isset($manifest['version'])) {
                $manifest['version'] = '1.0.0';
            }
            if (!isset($manifest['name'])) {
                $manifest['name'] = 'dummy/dummy';
            }
            $loader->load($manifest);
        } catch (InvalidPackageException $e) {
            $errors = array_merge($errors, $e->getErrors());
        }

        $warnings = array_merge($warnings, $loader->getWarnings());

        return array($errors, $publishErrors, $warnings);
    }

    /**
     * Validates the schema of the current json file according to composer-schema.json rules
     *
     * @throws JsonValidationException
     * @return bool true on success
     */
    public function validateSchema(array $manifest)
    {
        $data = $manifest;

        $schemaFile = self::COMPOSER_SCHEMA_PATH;
        $schemaData = (object) ['$ref' => $schemaFile];

        $schemaData->additionalProperties = true;
        $schemaData->required = array();

        $validator = new \JsonSchema\Validator();
        $validator->check($data, $schemaData);

        if (!$validator->isValid()) {
            $errors = [];
            foreach ($validator->getErrors() as $error) {
                $errors[] = ($error['property'] ? $error['property'].' : ' : '') . $error['message'];
            }

            throw new JsonValidationException('composer.json does not match the expected JSON schema', $errors);
        }

        return true;
    }
}
