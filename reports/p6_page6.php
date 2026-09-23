<?php
/**
 * P6 Report - Page 6: Teacher and Parent Comments
 */
?>
<div class="page behavior-comments-page <?= $student !== end($students_to_print) ? 'page-break' : '' ?>">
    <div class="p6-container">
        <!-- Teacher Comments Section -->
        <h3 class="text-center font-bold mb-4" style="font-size: 18px;">ความคิดเห็นและข้อเสนอแนะของครูประจำชั้น</h3>
        
        <table class="p6-table mb-4">
            <?php
            $categories = [
                'ด้านหน้าที่รับผิดชอบ ความเอาใจใส่การเรียน',
                'ด้านการใช้เวลาว่าง',
                'ด้านความสัมพันธ์กับ บุคคลรอบข้าง',
                'ด้านอุปนิสัย บุคลิกภาพ',
                'ด้านสุขภาพ'
            ];
            
            // Map database categories to display categories
            $db_to_display = [
                'หน้าที่รับผิดชอบ ความเอาใจใส่การเรียน' => 'ด้านหน้าที่รับผิดชอบ ความเอาใจใส่การเรียน',
                'การใช้เวลาว่าง' => 'ด้านการใช้เวลาว่าง',
                'ความสัมพันธ์กับบุคคลรอบข้าง' => 'ด้านความสัมพันธ์กับ บุคคลรอบข้าง',
                'อุปนิสัย บุคลิกภาพ' => 'ด้านอุปนิสัย บุคลิกภาพ',
                'สุขภาพ' => 'ด้านสุขภาพ'
            ];

            // Organize behavior data
            $behavior_map = [];
            foreach ($behavior_comments as $bc) {
                $display_name = $db_to_display[$bc['category_name']] ?? $bc['category_name'];
                $behavior_map[$display_name] = $bc['behavior_text'];
            }

            foreach ($categories as $cat):
                $text = $behavior_map[$cat] ?? '';
            ?>
            <tr style="height: 65px;">
                <td class="font-bold" style="width: 25%; padding: 5px 10px; text-align: center; line-height: 1.3;"><?= $cat ?></td>
                <td class="text-left" style="padding: 10px 15px; vertical-align: top;">
                    <?= nl2br(htmlspecialchars($text)) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if ($teacher_name_2): ?>
        <div style="display: flex; justify-content: flex-end; gap: 40px; margin-top: 35px; text-align: center; font-size: 14px; margin-right: 5%;">
            <div>
                <p>ลงชื่อ...................................ครูประจำชั้น</p>
                <p style="margin-top: 4px;">( <?= $teacher_name_1 ?> )</p>
            </div>
            <div>
                <p>ลงชื่อ...................................ครูประจำชั้น</p>
                <p style="margin-top: 4px;">( <?= $teacher_name_2 ?> )</p>
            </div>
        </div>
        <?php else: ?>
        <div class="sig-block" style="margin-top: 35px; text-align: center; margin-left: 45%; font-size: 14px;">
            <p>ลงชื่อ...................................ครูประจำชั้น/ครูที่ปรึกษา</p>
            <p style="margin-top: 4px;">( <?= $teacher_name_1 ?> )</p>
        </div>
        <?php endif; ?>

        <!-- Parent Comments Section -->
        <h3 class="text-center font-bold mb-4" style="font-size: 18px; margin-top: 45px;">ความคิดเห็นและข้อเสนอแนะของผู้ปกครอง</h3>
        
        <table class="p6-table mb-4">
            <?php 
            $parent_map = [
                'ด้านหน้าที่รับผิดชอบ ความเอาใจใส่การเรียน' => $parent_feedback['responsibility_comment'] ?? '',
                'ด้านการใช้เวลาว่าง' => $parent_feedback['spare_time_comment'] ?? '',
                'ด้านความสัมพันธ์กับ บุคคลรอบข้าง' => $parent_feedback['relationship_comment'] ?? '',
                'ด้านอุปนิสัย บุคลิกภาพ' => $parent_feedback['personality_comment'] ?? '',
                'ด้านสุขภาพ' => $parent_feedback['health_comment'] ?? ''
            ];

            foreach ($categories as $cat): 
                $p_text = $parent_map[$cat] ?? '';
            ?>
            <tr style="height: 65px;">
                <td class="font-bold" style="width: 25%; padding: 5px 10px; text-align: center; line-height: 1.3;"><?= $cat ?></td>
                <td class="text-left" style="padding: 10px 15px; vertical-align: top;">
                    <?= nl2br(htmlspecialchars($p_text)) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="sig-block" style="margin-top: 35px; text-align: center; margin-left: 45%;">
            ลงชื่อ........................................................ผู้ปกครอง
            <div style="margin-top: 10px;">
                (.......................................................)
            </div>
        </div>
    </div>
</div>

<style>
    .behavior-comments-page {
        padding: 20mm;
    }
    .behavior-comments-page .p6-table td {
        border: 1px solid black;
        font-size: 14px;
    }
</style>
