<?php

namespace GlobalPayments\Api\Tests\Unit\Entities;

use GlobalPayments\Api\Entities\InstallmentData;
use GlobalPayments\Api\Entities\InstallmentTerms;
use GlobalPayments\Api\Entities\Terms;
use GlobalPayments\Api\PaymentMethods\Installment;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Visa Installment entities
 * Tests entity creation and property assignment without API calls
 */
class VisaInstallmentTest extends TestCase
{
    // ========================================================================
    // Test 1: InstallmentData entity creation
    // ========================================================================
    
    public function testInstallmentDataCreation()
    {
        $installmentData = new InstallmentData();
        $installmentData->program = 'VIS';
        $installmentData->funding_mode = 'MERCHANT';
        $installmentData->eligible_plans = ['PLAN_A', 'PLAN_B'];
        
        $this->assertEquals('VIS', $installmentData->program);
        $this->assertEquals('MERCHANT', $installmentData->funding_mode);
        $this->assertIsArray($installmentData->eligible_plans);
        $this->assertCount(2, $installmentData->eligible_plans);
        $this->assertEquals('PLAN_A', $installmentData->eligible_plans[0]);
    }
    
    // ========================================================================
    // Test 2: InstallmentTerms entity creation
    // ========================================================================
    
    public function testInstallmentTermsCreation()
    {
        $terms = new InstallmentTerms();
        $terms->time_unit = 'MONTH';
        $terms->max_time_unit_number = 12;
        $terms->max_amount = '1000.00';
        
        $this->assertEquals('MONTH', $terms->time_unit);
        $this->assertEquals(12, $terms->max_time_unit_number);
        $this->assertEquals('1000.00', $terms->max_amount);
    }
    
    // ========================================================================
    // Test 3: InstallmentData with nested InstallmentTerms
    // ========================================================================
    
    public function testInstallmentDataWithTerms()
    {
        $terms = new InstallmentTerms();
        $terms->time_unit = 'MONTH';
        $terms->max_time_unit_number = 6;
        $terms->max_amount = '500.00';
        
        $installmentData = new InstallmentData();
        $installmentData->program = 'VIS';
        $installmentData->funding_mode = 'ISSUER';
        $installmentData->terms = $terms;
        
        $this->assertNotNull($installmentData->terms);
        $this->assertInstanceOf(InstallmentTerms::class, $installmentData->terms);
        $this->assertEquals('MONTH', $installmentData->terms->time_unit);
        $this->assertEquals(6, $installmentData->terms->max_time_unit_number);
    }
    
    // ========================================================================
    // Test 4: Terms response entity
    // ========================================================================
    
    public function testTermsResponseEntity()
    {
        $terms = new Terms();
        $terms->id = 'TERM_123';
        $terms->time_unit = 'MONTH';
        $terms->max_time_unit_number = 18;
        $terms->max_amount = '2000.00';
        
        $this->assertEquals('TERM_123', $terms->id);
        $this->assertEquals('MONTH', $terms->time_unit);
        $this->assertEquals(18, $terms->max_time_unit_number);
        $this->assertEquals('2000.00', $terms->max_amount);
    }
    
    // ========================================================================
    // Test 5: Installment payment method entity
    // ========================================================================
    
    public function testInstallmentPaymentMethodEntity()
    {
        $installment = new Installment();
        $installment->id = 'INS_123456';
        $installment->program = 'VIS';
        $installment->funding_mode = 'MERCHANT';
        $installment->eligible_plans = ['PLAN_X', 'PLAN_Y', 'PLAN_Z'];
        $installment->status = 'ACTIVE';
        $installment->amount = '999.99';
        $installment->currency = 'USD';
        
        $this->assertEquals('INS_123456', $installment->id);
        $this->assertEquals('VIS', $installment->program);
        $this->assertEquals('MERCHANT', $installment->funding_mode);
        $this->assertIsArray($installment->eligible_plans);
        $this->assertCount(3, $installment->eligible_plans);
        $this->assertEquals('ACTIVE', $installment->status);
        $this->assertEquals('999.99', $installment->amount);
        $this->assertEquals('USD', $installment->currency);
    }
    
    // ========================================================================
    // Test 6: Nullable fields default to null
    // ========================================================================
    
    public function testNullableFieldsDefaultToNull()
    {
        $installmentData = new InstallmentData();
        
        $this->assertNull($installmentData->funding_mode);
        $this->assertNull($installmentData->terms);
        $this->assertNull($installmentData->eligible_plans);
    }
    
    // ========================================================================
    // Test 7: InstallmentTerms nullable fields
    // ========================================================================
    
    public function testInstallmentTermsNullableFields()
    {
        $terms = new InstallmentTerms();
        
        $this->assertNull($terms->time_unit);
        $this->assertNull($terms->max_time_unit_number);
        $this->assertNull($terms->max_amount);
    }
    
    // ========================================================================
    // Test 8: Backward compatibility - existing fields still work
    // ========================================================================
    
    public function testBackwardCompatibilityExistingFields()
    {
        $installmentData = new InstallmentData();
        $installmentData->program = 'SIP'; // Mexico installment
        $installmentData->mode = 'PARTIAL';
        $installmentData->count = '3';
        $installmentData->grace_period_count = '1';
        
        // New Visa fields are null
        $this->assertNull($installmentData->funding_mode);
        $this->assertNull($installmentData->terms);
        
        // Existing fields work
        $this->assertEquals('SIP', $installmentData->program);
        $this->assertEquals('PARTIAL', $installmentData->mode);
        $this->assertEquals('3', $installmentData->count);
        $this->assertEquals('1', $installmentData->grace_period_count);
    }
    
    // ========================================================================
    // Test 9: Full Visa installment configuration
    // ========================================================================
    
    public function testFullVisaInstallmentConfiguration()
    {
        $terms = new InstallmentTerms();
        $terms->time_unit = 'MONTH';
        $terms->max_time_unit_number = 24;
        $terms->max_amount = '5000.00';
        
        $installmentData = new InstallmentData();
        $installmentData->program = 'VIS';
        $installmentData->mode = 'FULL';
        $installmentData->count = '12';
        $installmentData->funding_mode = 'ISSUER';
        $installmentData->terms = $terms;
        $installmentData->eligible_plans = ['PREMIUM', 'STANDARD', 'BASIC'];
        
        // Verify all fields
        $this->assertEquals('VIS', $installmentData->program);
        $this->assertEquals('FULL', $installmentData->mode);
        $this->assertEquals('12', $installmentData->count);
        $this->assertEquals('ISSUER', $installmentData->funding_mode);
        $this->assertNotNull($installmentData->terms);
        $this->assertEquals('MONTH', $installmentData->terms->time_unit);
        $this->assertEquals(24, $installmentData->terms->max_time_unit_number);
        $this->assertEquals('5000.00', $installmentData->terms->max_amount);
        $this->assertCount(3, $installmentData->eligible_plans);
    }
    
    // ========================================================================
    // Test 10: Terms backward compatibility fields
    // ========================================================================
    
    public function testTermsBackwardCompatibilityFields()
    {
        $terms = new Terms();
        
        // Old field names (existing functionality)
        $terms->id = 'TERM_OLD';
        $terms->timeUnit = 'DAY';
        $terms->timeUnitNumbers = '30';
        
        // New Visa field names
        $terms->time_unit = 'MONTH';
        $terms->max_time_unit_number = 6;
        $terms->max_amount = '100.00';
        
        // Both sets of fields coexist
        $this->assertEquals('TERM_OLD', $terms->id);
        $this->assertEquals('DAY', $terms->timeUnit);
        $this->assertEquals('30', $terms->timeUnitNumbers);
        $this->assertEquals('MONTH', $terms->time_unit);
        $this->assertEquals(6, $terms->max_time_unit_number);
        $this->assertEquals('100.00', $terms->max_amount);
    }
}
