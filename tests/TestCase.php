<?php

declare(strict_types=1);

namespace CarlLee\EcPay\Core\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * 測試基礎類別。
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * 測試用 HashKey。
     */
    protected const TEST_HASH_KEY = 'ejCk326UnaZWKisg';

    /**
     * 測試用 HashIV。
     */
    protected const TEST_HASH_IV = 'q9jcZX8Ib9LM8wYk';

    /**
     * 測試用 MerchantID。
     */
    protected const TEST_MERCHANT_ID = '2000132';
}
