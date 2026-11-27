# Yireo_CleanAdminMenu

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-clean-admin-menu)

</div>
<!-- badges.specs.end -->


**This Magento 2 module adds all non-Magento root menu-items of the admin menu to a menu-item "Third Party". It doesn't use JavaScript to do this, but cleanly hooks into the model-class used by the core.**

### Installation
Install this package via composer:
```bash
composer require yireo/magento2-clean-admin-menu
```

Next, enable this module:
```bash
bin/magento module:enable Yireo_CleanAdminMenu
```

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CleanAdminMenu/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CleanAdminMenu/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CleanAdminMenu/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CleanAdminMenu/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CleanAdminMenu/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
