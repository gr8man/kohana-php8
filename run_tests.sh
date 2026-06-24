#!/bin/bash

echo "========================================="
echo "Running local test suite..."
echo "========================================="

# 1. Coding Standards
echo ""
echo "[1/5] Checking Coding Standards (PHP CS Fixer)..."
vendor/bin/php-cs-fixer fix --dry-run --verbose --ansi
CS_EXIT=$?

# 2. Static Analysis
echo ""
echo "[2/5] Running Static Analysis (PHPStan)..."
vendor/bin/phpstan analyse -c phpstan.neon
PHPSTAN_EXIT=$?

# 3. Rector Dry-run
echo ""
echo "[3/5] Checking Modernization Rules (Rector)..."
vendor/bin/rector process --dry-run
RECTOR_EXIT=$?

# 4. Static Analysis (Psalm)
echo ""
echo "[4/5] Running Static Analysis (Psalm)..."
vendor/bin/psalm --show-info=false
PSALM_EXIT=$?

# 5. Unit Tests
echo ""
echo "[5/5] Running Unit Tests (PHPUnit)..."
php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/phpunit
PHPUNIT_EXIT=$?

echo ""
echo "========================================="
echo "Test Results Summary:"
echo "========================================="

if [ $CS_EXIT -eq 0 ]; then
    echo "  - PHP CS Fixer:  PASSED"
else
    echo "  - PHP CS Fixer:  FAILED"
fi

if [ $PHPSTAN_EXIT -eq 0 ]; then
    echo "  - PHPStan:       PASSED"
else
    echo "  - PHPStan:       FAILED"
fi

if [ $RECTOR_EXIT -eq 0 ]; then
    echo "  - Rector:        PASSED"
else
    echo "  - Rector:        FAILED"
fi

if [ $PSALM_EXIT -eq 0 ]; then
    echo "  - Psalm:         PASSED"
else
    echo "  - Psalm:         FAILED"
fi

if [ $PHPUNIT_EXIT -eq 0 ]; then
    echo "  - PHPUnit:       PASSED"
else
    echo "  - PHPUnit:       FAILED"
fi
echo "========================================="

# Exit with non-zero if any test failed
if [ $CS_EXIT -ne 0 ] || [ $PHPSTAN_EXIT -ne 0 ] || [ $RECTOR_EXIT -ne 0 ] || [ $PSALM_EXIT -ne 0 ] || [ $PHPUNIT_EXIT -ne 0 ]; then
    exit 1
fi

exit 0
