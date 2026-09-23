<!-- หน้าที่ 5: ผลการประเมินภาวะโภชนาการ และ สรุปเวลาเรียน -->
<div class="page health-attendance-page">
    <h3 class="text-center" style="font-size: 18px; margin-bottom: 20px;">ผลการประเมินภาวะโภชนาการ</h3>
    
    <table class="p6-table" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30%;">น้ำหนัก - ส่วนสูง</th>
                <th colspan="2">ภาคเรียนที่ 1</th>
                <th colspan="2">ภาคเรียนที่ 2</th>
            </tr>
            <tr>
                <th style="width: 17.5%;">ครั้งที่ 1</th>
                <th style="width: 17.5%;">ครั้งที่ 2</th>
                <th style="width: 17.5%;">ครั้งที่ 1</th>
                <th style="width: 17.5%;">ครั้งที่ 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">วันที่ชั่งน้ำหนัก/วัดส่วนสูง</td>
                <td><?= !empty($health_data[1][1]['recorded_date']) ? formatThaiDate($health_data[1][1]['recorded_date'])['day'] . ' ' . formatThaiDate($health_data[1][1]['recorded_date'])['month'] . ' ' . formatThaiDate($health_data[1][1]['recorded_date'])['year'] : '-' ?></td>
                <td><?= !empty($health_data[1][2]['recorded_date']) ? formatThaiDate($health_data[1][2]['recorded_date'])['day'] . ' ' . formatThaiDate($health_data[1][2]['recorded_date'])['month'] . ' ' . formatThaiDate($health_data[1][2]['recorded_date'])['year'] : '-' ?></td>
                <td><?= !empty($health_data[2][1]['recorded_date']) ? formatThaiDate($health_data[2][1]['recorded_date'])['day'] . ' ' . formatThaiDate($health_data[2][1]['recorded_date'])['month'] . ' ' . formatThaiDate($health_data[2][1]['recorded_date'])['year'] : '-' ?></td>
                <td><?= !empty($health_data[2][2]['recorded_date']) ? formatThaiDate($health_data[2][2]['recorded_date'])['day'] . ' ' . formatThaiDate($health_data[2][2]['recorded_date'])['month'] . ' ' . formatThaiDate($health_data[2][2]['recorded_date'])['year'] : '-' ?></td>
            </tr>
            <tr>
                <td class="text-left">น้ำหนัก (กิโลกรัม)</td>
                <td><?= $health_data[1][1]['weight'] ?? '-' ?></td>
                <td><?= $health_data[1][2]['weight'] ?? '-' ?></td>
                <td><?= $health_data[2][1]['weight'] ?? '-' ?></td>
                <td><?= $health_data[2][2]['weight'] ?? '-' ?></td>
            </tr>
            <tr>
                <td class="text-left">ส่วนสูง (เซนติเมตร)</td>
                <td><?= $health_data[1][1]['height'] ?? '-' ?></td>
                <td><?= $health_data[1][2]['height'] ?? '-' ?></td>
                <td><?= $health_data[2][1]['height'] ?? '-' ?></td>
                <td><?= $health_data[2][2]['height'] ?? '-' ?></td>
            </tr>
            <tr>
                <th colspan="5" style="background: #f9f9f9; font-weight: bold;">ผลการประเมินภาวะโภชนาการตามเกณฑ์มาตรฐาน</th>
            </tr>
            <tr>
                <td class="text-left">น้ำหนักตามเกณฑ์อายุ</td>
                <td><?= $health_data[1][1]['weight_age_result'] ?? '-' ?></td>
                <td><?= $health_data[1][2]['weight_age_result'] ?? '-' ?></td>
                <td><?= $health_data[2][1]['weight_age_result'] ?? '-' ?></td>
                <td><?= $health_data[2][2]['weight_age_result'] ?? '-' ?></td>
            </tr>
            <tr>
                <td class="text-left">ส่วนสูงตามเกณฑ์อายุ</td>
                <td><?= $health_data[1][1]['height_age_result'] ?? '-' ?></td>
                <td><?= $health_data[1][2]['height_age_result'] ?? '-' ?></td>
                <td><?= $health_data[2][1]['height_age_result'] ?? '-' ?></td>
                <td><?= $health_data[2][2]['height_age_result'] ?? '-' ?></td>
            </tr>
            <tr>
                <td class="text-left">น้ำหนักตามเกณฑ์ส่วนสูง</td>
                <td><?= $health_data[1][1]['weight_height_result'] ?? '-' ?></td>
                <td><?= $health_data[1][2]['weight_height_result'] ?? '-' ?></td>
                <td><?= $health_data[2][1]['weight_height_result'] ?? '-' ?></td>
                <td><?= $health_data[2][2]['weight_height_result'] ?? '-' ?></td>
            </tr>
        </tbody>
    </table>

    <h3 class="text-center" style="font-size: 18px; margin-bottom: 20px;">สรุปเวลาเรียน</h3>
    
    <table class="p6-table">
        <thead>
            <tr>
                <th style="width: 30%;">เดือน</th>
                <th style="width: 20%;">เวลาเต็ม (วัน)</th>
                <th style="width: 20%;">เวลามา (วัน)</th>
                <th style="width: 15%;">คิดเป็นร้อยละ</th>
                <th style="width: 15%;">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $months_list = [
                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม',
                11 => 'พฤศจิกายน', 12 => 'ธันวาคม', 1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน'
            ];
            $total_school_days = 0;
            $total_present_days = 0;
            
            foreach ($months_list as $m_num => $m_name):
                $year_offset = ($m_num <= 4) ? 1 : 0;
                $y_val = (int)$year - 543 + $year_offset;
                $key = $y_val . '-' . $m_num;
                
                $t_days = $attendance_summary[$key]['total'] ?? 0;
                $p_days = $attendance_summary[$key]['present'] ?? 0;
                $percent = $t_days > 0 ? number_format(($p_days / $t_days) * 100, 2) : '-';
                
                $total_school_days += $t_days;
                $total_present_days += $p_days;
            ?>
            <tr>
                <td class="text-left"><?= $m_name ?></td>
                <td><?= $t_days > 0 ? $t_days : '-' ?></td>
                <td><?= $t_days > 0 ? $p_days : '-' ?></td>
                <td><?= $percent ?></td>
                <td></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background: #f9f9f9;">
                <td class="text-left">รวมตลอดปีการศึกษา</td>
                <td><?= $total_school_days ?></td>
                <td><?= $total_present_days ?></td>
                <td><?= $total_school_days > 0 ? number_format(($total_present_days / $total_school_days) * 100, 2) : '-' ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
