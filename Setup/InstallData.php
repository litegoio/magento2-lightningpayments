<?php
namespace LiteGoio\LightningPayments\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;


    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        /**
         * Remove previous attributes
         * \Magento\Sales\Model\Order::ENTITY
         */
        $attributes = [
            'litego_payment_request',
            'litego_payment_request_time',
            'litego_payment_request_time_expire',
            'litego_hash',
            'litego_sat_rate',
            'litego_sat_amount',
            'litego_charge_id'
        ];
        foreach ($attributes as $attr_to_remove){
            $salesSetup->removeAttribute('order', $attr_to_remove);

        }

        /**
         * Add 'NEW_ATTRIBUTE' attributes for order
         * \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
         * Magento\Sales\Setup\SalesSetup::_getAttributeColumnDefinition
         */
        $options = ['type' => 'text', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_payment_request', $options);

        $options = ['type' => 'datetime', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_payment_request_time', $options);

        $options = ['type' => 'datetime', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_payment_request_time_expire', $options);
        
        $options = ['type' => 'varchar', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_hash', $options);
        
        $options = ['type' => 'varchar', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_sat_rate', $options);
        
        $options = ['type' => 'varchar', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_sat_amount', $options);
        
        $options = ['type' => 'varchar', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'litego_charge_id', $options);


        /**
         * Modify standart magento tables columns
         */
        $connection = $setup->getConnection();
        /*
        $tableName = $setup->getTable('quote');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'store_to_base_rate',
                'store_to_quote_rate',
                'grand_total',
                'base_grand_total',
                'base_to_global_rate',
                'base_to_quote_rate',
                'subtotal',
                'base_subtotal',
                'subtotal_with_discount',
                'base_subtotal_with_discount'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('quote_item');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'price',
                'base_price',
                'custom_price',
                'discount_percent',
                'discount_amount',
                'base_discount_amount',
                'tax_percent',
                'tax_amount',
                'base_tax_amount',
                'row_total',
                'base_row_total',
                'row_total_with_discount',
                'base_tax_before_discount',
                'tax_before_discount',
                'original_custom_price',
                'base_cost',
                'price_incl_tax',
                'base_price_incl_tax',
                'row_total_incl_tax',
                'base_row_total_incl_tax',
                'discount_tax_compensation_amount',
                'base_discount_tax_compensation_amount',
                'weee_tax_applied_amount',
                'weee_tax_applied_row_amount',
                'weee_tax_disposition',
                'weee_tax_row_disposition',
                'base_weee_tax_applied_amount',
                'base_weee_tax_applied_row_amnt',
                'base_weee_tax_disposition',
                'base_weee_tax_row_disposition'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('quote_address');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'subtotal',
                'base_subtotal',
                'subtotal_with_discount',
                'base_subtotal_with_discount',
                'tax_amount',
                'base_tax_amount',
                'shipping_amount',
                'base_shipping_amount',
                'shipping_tax_amount',
                'base_shipping_tax_amount',
                'discount_amount',
                'base_discount_amount',
                'grand_total',
                'base_grand_total',
                'shipping_discount_amount',
                'base_shipping_discount_amount',
                'subtotal_incl_tax',
                'base_subtotal_total_incl_tax',
                'discount_tax_compensation_amount',
                'base_discount_tax_compensation_amount',
                'shipping_discount_tax_compensation_amount',
                'base_shipping_discount_tax_compensation_amnt',
                'shipping_incl_tax',
                'base_shipping_incl_tax'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('quote_address_item');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'discount_amount',
                'tax_amount',
                'row_total',
                'base_row_total',
                'row_total_with_discount',
                'base_discount_amount',
                'base_tax_amount',
                'price',
                'discount_percent',
                'tax_percent',
                'base_price',
                'base_cost',
                'price_incl_tax',
                'base_price_incl_tax',
                'row_total_incl_tax',
                'base_row_total_incl_tax',
                'discount_tax_compensation_amount',
                'base_discount_tax_compensation_amount'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('sales_order');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'base_discount_amount',
                'base_discount_canceled',
                'base_discount_invoiced',
                'base_discount_refunded',
                'base_grand_total',
                'base_shipping_amount',
                'base_shipping_canceled',
                'base_shipping_invoiced',
                'base_shipping_refunded',
                'base_shipping_tax_amount',
                'base_shipping_tax_refunded',
                'base_subtotal',
                'base_subtotal_canceled',
                'base_subtotal_invoiced',
                'base_subtotal_refunded',
                'base_tax_amount',
                'base_tax_canceled',
                'base_tax_invoiced',
                'base_tax_refunded',
                'base_to_global_rate',
                'base_to_order_rate',
                'base_total_canceled',
                'base_total_invoiced',
                'base_total_invoiced_cost',
                'base_total_offline_refunded',
                'base_total_online_refunded',
                'base_total_paid',
                'base_total_refunded',
                'discount_amount',
                'discount_canceled',
                'discount_invoiced',
                'discount_refunded',
                'grand_total',
                'shipping_amount',
                'shipping_canceled',
                'shipping_invoiced',
                'shipping_refunded',
                'shipping_tax_amount',
                'shipping_tax_refunded',
                'store_to_base_rate',
                'store_to_order_rate',
                'subtotal',
                'subtotal_canceled',
                'subtotal_invoiced',
                'subtotal_refunded',
                'tax_amount',
                'tax_canceled',
                'tax_invoiced',
                'tax_refunded',
                'total_canceled',
                'total_invoiced',
                'total_offline_refunded',
                'total_online_refunded',
                'total_paid',
                'total_refunded',
                'adjustment_negative',
                'adjustment_positive',
                'base_adjustment_negative',
                'base_adjustment_positive',
                'base_shipping_discount_amount',
                'base_subtotal_incl_tax',
                'base_total_due',
                'payment_authorization_amount',
                'shipping_discount_amount',
                'subtotal_incl_tax',
                'total_due',
                'discount_tax_compensation_amount',
                'base_discount_tax_compensation_amount',
                'shipping_discount_tax_compensation_amount',
                'base_shipping_discount_tax_compensation_amnt',
                'discount_tax_compensation_invoiced',
                'base_discount_tax_compensation_invoiced',
                'discount_tax_compensation_refunded',
                'base_discount_tax_compensation_refunded',
                'shipping_incl_tax',
                'base_shipping_incl_tax'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('sales_order_item');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'base_cost',
                'price',
                'base_price',
                'original_price',
                'base_original_price',
                'tax_percent',
                'tax_amount',
                'base_tax_amount',
                'tax_invoiced',
                'base_tax_invoiced',
                'discount_percent',
                'discount_amount',
                'base_discount_amount',
                'discount_invoiced',
                'base_discount_invoiced',
                'amount_refunded',
                'base_amount_refunded',
                'row_total',
                'base_row_total',
                'row_invoiced',
                'base_row_invoiced',
                'base_tax_before_discount',
                'tax_before_discount',
                'price_incl_tax',
                'base_price_incl_tax',
                'row_total_incl_tax',
                'base_row_total_incl_tax',
                'discount_tax_compensation_amount',
                'base_discount_tax_compensation_amount',
                'discount_tax_compensation_invoiced',
                'base_discount_tax_compensation_invoiced',
                'discount_tax_compensation_refunded',
                'base_discount_tax_compensation_refunded',
                'tax_canceled',
                'discount_tax_compensation_canceled',
                'tax_refunded',
                'base_tax_refunded',
                'discount_refunded',
                'base_discount_refunded',
                'weee_tax_applied_amount',
                'weee_tax_applied_row_amount',
                'weee_tax_disposition',
                'weee_tax_row_disposition',
                'base_weee_tax_applied_amount',
                'base_weee_tax_applied_row_amnt',
                'base_weee_tax_disposition',
                'base_weee_tax_row_disposition'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('sales_order_grid');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'base_grand_total',
                'base_total_paid',
                'grand_total',
                'total_paid',
                'subtotal',
                'shipping_and_handling',
                'total_refunded'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('sales_order_payment');
        if ($setup->getConnection()->isTableExists($tableName) == true)
        {
            $columns = [
                'base_shipping_captured',
                'shipping_captured',
                'amount_refunded',
                'base_amount_paid',
                'amount_canceled',
                'base_amount_authorized',
                'base_amount_paid_online',
                'base_amount_refunded_online',
                'base_shipping_amount',
                'shipping_amount',
                'amount_paid',
                'amount_authorized',
                'base_amount_ordered',
                'base_shipping_refunded',
                'shipping_refunded',
                'base_amount_refunded',
                'amount_ordered',
                'base_amount_canceled'
            ];

            foreach($columns as $column)
            {
                if ($connection->tableColumnExists($tableName, $column) === true) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            //'length' => "24,12"
                            'scale'     => 12,
                            'precision' => 24,
                        ]
                    );
                }
            }
        }

        */

        $setup->endSetup();
    }
}

