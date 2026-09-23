<?php
/**
 * Utility class for calculating Thai Ministry of Public Health growth standards
 * for children aged 5-19 years.
 */
class GrowthCalc {
    /**
     * Calculate Weight-for-Age result
     */
    public static function getWeightForAge($gender, $ageMonths, $weight) {
        // Simplified logic based on Thai Growth Charts (2020)
        // Gender: 'male' or 'female'
        // Age: months
        
        $ageYears = $ageMonths / 12;
        
        if ($gender === 'ชาย' || $gender === 'male') {
            // Male 5-19 years simplified normal ranges (P5 to P95)
            $min = 10 + ($ageYears * 1.5); // Very rough linear approximation
            $max = 15 + ($ageYears * 3.5);
            
            // More specific for school age
            if ($ageYears >= 6 && $ageYears <= 12) {
                $min = 14 + ($ageYears - 6) * 2.5;
                $max = 24 + ($ageYears - 6) * 5.5;
            }
        } else {
            // Female
            $min = 10 + ($ageYears * 1.4);
            $max = 15 + ($ageYears * 3.3);
            
            if ($ageYears >= 6 && $ageYears <= 12) {
                $min = 13 + ($ageYears - 6) * 2.4;
                $max = 23 + ($ageYears - 6) * 5.8;
            }
        }
        
        if ($weight < $min * 0.8) return 'น้อยกว่าเกณฑ์';
        if ($weight < $min) return 'ค่อนข้างน้อย';
        if ($weight <= $max) return 'ตามเกณฑ์';
        if ($weight <= $max * 1.2) return 'ค่อนข้างมาก';
        return 'เกินเกณฑ์';
    }

    /**
     * Calculate Height-for-Age result
     */
    public static function getHeightForAge($gender, $ageMonths, $height) {
        $ageYears = $ageMonths / 12;
        
        if ($gender === 'ชาย' || $gender === 'male') {
            $min = 105 + ($ageYears - 5) * 5;
            $max = 115 + ($ageYears - 5) * 7;
            
            if ($ageYears >= 6 && $ageYears <= 12) {
                $min = 110 + ($ageYears - 6) * 4.5;
                $max = 122 + ($ageYears - 6) * 6.5;
            }
        } else {
            $min = 104 + ($ageYears - 5) * 5;
            $max = 114 + ($ageYears - 5) * 7;
            
            if ($ageYears >= 6 && $ageYears <= 12) {
                $min = 109 + ($ageYears - 6) * 4.5;
                $max = 121 + ($ageYears - 6) * 6.8;
            }
        }
        
        if ($height < $min * 0.95) return 'เตี้ย';
        if ($height < $min) return 'ค่อนข้างเตี้ย';
        if ($height <= $max) return 'ตามเกณฑ์';
        if ($height <= $max * 1.05) return 'ค่อนข้างสูง';
        return 'สูง';
    }

    /**
     * Calculate Weight-for-Height result (using BMI-for-age as proxy for school children)
     */
    public static function getWeightForHeight($gender, $ageMonths, $weight, $height) {
        if ($height <= 0) return '-';
        
        $bmi = $weight / (($height / 100) ** 2);
        $ageYears = $ageMonths / 12;
        
        // Simplified BMI-for-age thresholds for Thai children
        $normalMin = 13.5;
        $normalMax = 18.5 + ($ageYears - 6) * 0.8;
        
        if ($ageYears > 12) {
            $normalMax = 22 + ($ageYears - 12) * 1.2;
        }

        if ($bmi < $normalMin * 0.9) return 'ผอม';
        if ($bmi < $normalMin) return 'ค่อนข้างผอม';
        if ($bmi <= $normalMax) return 'สมส่วน';
        if ($bmi <= $normalMax * 1.15) return 'ท้วม';
        if ($bmi <= $normalMax * 1.3) return 'เริ่มอ้วน';
        return 'อ้วน';
    }
}
