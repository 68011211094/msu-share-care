<?php

/**
 * บันทึกภาพที่ผู้ใช้เลือกในฟอร์ม (ไม่บังคับ — ขนาดไม่เกิน 2 MB, รองรับ JPG/PNG/GIF)
 *
 * @param array $errors ตำแหน่งเก็บข้อความ error (ส่งแบบ reference)
 * @return string|null คืน path เช่น "uploads/xxxx.png" หรือ null ถ้าไม่ได้เลือกไฟล์
 */
function handle_image_upload(&$errors)
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'อัปโหลดรูปภาพไม่สำเร็จ โปรดลองอีกครั้ง';
        return null;
    }

    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        $errors[] = 'รูปภาพต้องมีขนาดไม่เกิน 2 MB';
        return null;
    }

    $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'ไฟล์ที่เลือกไม่ใช่ภาพจริง (รองรับเฉพาะ JPG, PNG และ GIF)';
        return null;
    }

    $allowedExtensions = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
    ];
    if (!isset($allowedExtensions[$imageInfo[2]])) {
        $errors[] = 'รองรับเฉพาะไฟล์ภาพ JPG, PNG และ GIF';
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedExtensions[$imageInfo[2]];
    $destination = __DIR__ . '/../uploads/' . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        $errors[] = 'บันทึกรูปภาพไม่สำเร็จ โปรดลองอีกครั้ง';
        return null;
    }

    return 'uploads/' . $filename;
}

/**
 * ลบไฟล์ภาพในโฟลเดอร์ uploads (ใช้ตอนเปลี่ยน/ลบรูป หรือลบประกาศ)
 *
 * @param string|null $image ค่าในคอลัมน์ items.image เช่น "uploads/xxxx.png"
 */
function delete_uploaded_image($image)
{
    if (!is_string($image) || $image === '') {
        return;
    }

    $file = __DIR__ . '/../uploads/' . basename($image);
    if (is_file($file)) {
        @unlink($file);
    }
}