<?php
/**
 * @copyright 2019 Zyxware
 */
namespace Zyxware\Worldpay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    const WORLDPAY_PAYMENT = 'worldpay_payment';
    const WORLDPAY_TOKEN = 'worldpay_token';
    const WORLDPAY_RECURRING = 'worldpay_recurring';

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists(self::WORLDPAY_PAYMENT)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable(self::WORLDPAY_PAYMENT)
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,
                'unsigned' => true],
                'Order Id'
            )
            ->addColumn(
                'payment_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Payment Status'
            )
            ->addColumn(
                'payment_model',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable' => false],
                'Payment Model'
            )
            ->addColumn(
                'payment_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payment Type'
            )
            ->addColumn(
                'mac_verified',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                [],
                'MAC Verified'
            )
            ->addColumn(
                'merchant_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [],
                'Merchant Id'
            )
            ->addColumn(
                '3d_verified',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                [],
                '3D Secure Verified'
            )
            ->addColumn(
                'risk_score',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Risk Score'
            )
            ->addColumn(
                'method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Method'
            )
            ->addColumn(
                'card_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Card Number'
            )
            ->addColumn(
                'avs_result',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'AVS Result'
            )
            ->addColumn(
                'cvc_result',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'CVC Result'
            )
            ->addColumn(
                '3d_secure_result',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                '3D Secure Result'
            )->addColumn(
                'worldpay_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '40',
                [],
                'WorldPay Order Id'
            )
            ->addColumn(
                'risk_provider',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '24',
                [],
                'Risk Provider'
            )
            ->addColumn(
                'risk_provider_score',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '8,4',
                [],
                'Risk Provider Score'
            )
            ->addColumn(
                'risk_provider_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '20',
                [],
                'Risk Provider Id'
            )
            ->addColumn(
                'risk_provider_threshold',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '4',
                [],
                'Risk Provider Threshold'
            )
            ->addColumn(
                'risk_provider_final',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '4',
                [],
                'Risk Provider Final'
            )
            ->addIndex(
                $installer->getIdxName(self::WORLDPAY_PAYMENT, ['order_id']),
                ['order_id']
            )
            ->addIndex(
                $installer->getIdxName(self::WORLDPAY_PAYMENT, ['worldpay_order_id']),
                ['worldpay_order_id']
            )

            ->setComment('Payment Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists(self::WORLDPAY_RECURRING)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable(self::WORLDPAY_RECURRING)
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                'unsigned' => true,
                'nullable' => false
                ],
                'Customer Id'
            )
            ->addColumn(
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Payment Id'
                )
            ->addColumn(
                'order_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable'=> false],
                'Order Code'
                )
            ->addColumn(
                'merchant_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false,'unsigned' => true],
                'Merchant Code'
                )
            ->setComment('Reucurring Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        /*
        *Token store
        */
        if (!$installer->tableExists(self::WORLDPAY_TOKEN)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable(self::WORLDPAY_TOKEN)
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Id'
            )
            ->addColumn(
                'token_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                21,
                ['nullable' => false],
                'Token Code'
                )
            ->addColumn(
                'token_expiry_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable'=> false],
                'Token Expiry Date'
                )
            ->addColumn(
                'token_reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Token Reason'
                )
            ->addColumn(
                'card_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Obfuscated Card number'
                )
            ->addColumn(
                'cardholder_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Card Holder Name'
                )
            ->addColumn(
                'card_expiry_month',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Card Expiry Month'
                )
            ->addColumn(
                'card_expiry_year',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Card Expiry Year'
                )
            ->addColumn(
                'method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Payment method used'
                )
            ->addColumn(
                'card_brand',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Card Brand'
                )
            ->addColumn(
                'card_sub_brand',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Card Sub Brand'
                )
            ->addColumn(
                'card_issuer_country_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Card Issuer Country Code'
                )
            ->addColumn(
                'merchant_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => false,'unsigned' => true],
                'Merchant Code'
                )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                'unsigned' => true,
                'nullable' => false
                ],
                'Customer Id'
                )
            ->addColumn(
                'authenticated_shopper_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Authenticated Shopper ID'
                )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Created At'
                )
            ->addIndex(
                $installer->getIdxName(self::WORLDPAY_TOKEN, ['token_code']),
                ['token_code'],
                 \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                )
            ->addIndex(
                $installer->getIdxName(self::WORLDPAY_TOKEN, ['customer_id']),
                ['customer_id'],
                 \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                )
            ->setComment('Token Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();

    }
}
