<!-- หน้าที่ 1: ผลการเรียน (Score Summary) -->
<div class="page p6-container">
    <div class="p6-header">
        <img src="<?= !empty($logo_url) ? $logo_url : $garuda_url ?>" class="p6-logo-left" referrerPolicy="no-referrer">
        <h3 style="margin: 0; font-size: 18px; padding-top: 10px;">แบบรายงานประจำตัวนักเรียน : ผลการพัฒนาคุณภาพผู้เรียนรายบุคคล (ปพ.6)</h3>
        <p style="margin: 5px 0; font-size: 16px;">โรงเรียน<?= $school_name ?> <?= $affiliation ?></p>
        <p style="margin: 5px 0; font-size: 16px;">ชั้นประถมศึกษาปีที่ <?= $clean_level ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <div style="margin-bottom: 10px; font-size: 14px; display: flex; justify-content: space-between;">
        <div>ชื่อ-สกุล <span class="dotted-line" style="min-width: 250px;"><?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?></span></div>
        <div>เลขประจำตัว <span class="dotted-line" style="min-width: 100px;"><?= $student['student_code'] ?></span></div>
        <div>เลขที่ <span class="dotted-line" style="min-width: 50px;"><?= array_search($student['id'], array_column($students_to_print, 'id')) + 1 ?></span></div>
    </div>

    <table class="p6-table">
        <thead>
            <tr>
                <th style="width: 80px;">รหัสวิชา</th>
                <th>รายวิชา</th>
                <th style="width: 70px;">เวลาเรียน<br>(ชั่วโมง/ปี)</th>
                <th style="width: 60px;">คะแนน<br>เต็ม</th>
                <th style="width: 60px;">ค่าเฉลี่ย<br>ในชั้นเรียน</th>
                <th style="width: 60px;">คะแนน<br>ที่ได้</th>
                <th style="width: 60px;">คิดเป็น<br>ร้อยละ</th>
                <th style="width: 70px;">ระดับผล<br>การเรียน</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_hours = 0;
            $total_score = 0;
            $count_subjects = 0;
            $total_percent = 0;
            $display_grades = array_pad($grades, 15, null);
            foreach ($display_grades as $g): 
                if ($g) {
                    $total_hours += $g['hours'];
                    $total_score += $g['score_total'];
                    $total_percent += $g['score_percent'];
                    $count_subjects++;
                }
            ?>
            <tr style="height: 25px;">
                <td><?= $g ? $g['code'] : '' ?></td>
                <td class="text-left"><?= $g ? $g['name'] : '' ?></td>
                <td><?= $g ? $g['hours'] : '' ?></td>
                <td><?= $g ? '100.00' : '' ?></td>
                <td><?= $g ? '-' : '' ?></td>
                <td><?= $g ? number_format($g['score_total'], 2) : '' ?></td>
                <td><?= $g ? number_format($g['score_percent'], 2) : '' ?></td>
                <td><?= $g ? $g['grade'] : '' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background: #eee;">
                <td colspan="2">รวม</td>
                <td><?= $total_hours ?></td>
                <td><?= number_format($count_subjects * 100, 2) ?></td>
                <td>-</td>
                <td><?= number_format($total_score, 2) ?></td>
                <td><?= $count_subjects > 0 ? number_format($total_percent / $count_subjects, 2) : '' ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-left">
            <table class="summary-table">
                <tr>
                    <td class="text-left">คะแนนคิดเป็นร้อยละ</td>
                    <td style="width: 80px;"><?= $count_subjects > 0 ? number_format($total_percent / $count_subjects, 2) : '-' ?></td>
                </tr>
                <tr>
                    <td class="text-left">คะแนนรวมได้ลำดับที่</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td class="text-left">ผลการเรียนเฉลี่ย</td>
                    <td><?= $count_subjects > 0 ? number_format($total_score / ($count_subjects * 100) * 4, 2) : '-' ?></td>
                </tr>
                <tr>
                    <td class="text-left">ผลการเรียนเฉลี่ยได้ลำดับที่</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td colspan="2" class="font-bold" style="background: #eee;">ผลการประเมินกิจกรรมพัฒนาผู้เรียน</td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;&nbsp;&nbsp;แนะแนว</td>
                    <td><?= formatPassFail($ld_result['guidance_result']) ?></td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;&nbsp;&nbsp;ลูกเสือ-เนตรนารี</td>
                    <td><?= formatPassFail($ld_result['scout_result']) ?></td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;&nbsp;&nbsp;ชุมนุม กีฬาและนันทนาการ</td>
                    <td><?= formatPassFail($ld_result['club_result']) ?></td>
                </tr>
                <tr>
                    <td class="text-left">&nbsp;&nbsp;&nbsp;กิจกรรมเพื่อสังคมและสาธารณประโยชน์</td>
                    <td><?= formatPassFail($ld_result['social_result']) ?></td>
                </tr>
                <tr>
                    <td class="text-left">ผลการประเมินคุณลักษณะอันพึงประสงค์</td>
                    <td><?= getResultText($behavior['average_score'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td class="text-left">ผลการประเมินการอ่าน คิดวิเคราะห์และเขียน</td>
                    <td><?= getResultText($analytical['average_score'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td class="text-left">ผลการประเมินสมรรถนะสำคัญของผู้เรียน</td>
                    <td><?= getResultText($competency['average_score'] ?? 0) ?></td>
                </tr>
            </table>
        </div>
        <div class="summary-right">
            <?php if ($teacher_name_2): ?>
            <div style="display: flex; justify-content: space-around; gap: 10px; margin-top: 15px; width: 100%;">
                <div class="sig-block" style="margin-top: 0; flex: 1;">
                    <p>ลงชื่อ..........................................................</p>
                    <p>( <?= $teacher_name_1 ?> )</p>
                    <p>ครูประจำชั้น/ครูที่ปรึกษา</p>
                </div>
                <div class="sig-block" style="margin-top: 0; flex: 1;">
                    <p>ลงชื่อ..........................................................</p>
                    <p>( <?= $teacher_name_2 ?> )</p>
                    <p>ครูประจำชั้น/ครูที่ปรึกษา</p>
                </div>
            </div>
            <?php else: ?>
            <div class="sig-block">
                <p>ลงชื่อ..........................................................</p>
                <p>( <?= $teacher_name_1 ?> )</p>
                <p>ครูประจำชั้น/ครูที่ปรึกษา</p>
            </div>
            <?php endif; ?>
            <div class="sig-block">
                <p>ลงชื่อ..........................................................</p>
                <p>( <?= $acad_name ?> )</p>
                <p><?= $acad_pos ?></p>
            </div>
            <div class="sig-block">
                <p>ลงชื่อ..........................................................</p>
                <p>( <?= $director_name ?> )</p>
                <p>ผู้อำนวยการโรงเรียน</p>
            </div>
            <div class="sig-block" style="margin-top: 25px;">
                <p>ลงชื่อ..........................................................</p>
                <p>(..........................................................)</p>
                <p>ผู้ปกครองนักเรียน</p>
            </div>
        </div>
    </div>
</div>
