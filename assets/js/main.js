jQuery(function ($) {
    var $toggle = $('.nav-toggle');
    var $nav = $('.site-nav');

    $toggle.on('click', function () {
        var isOpen = $nav.toggleClass('is-open').hasClass('is-open');
        $toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
    });

    var closeNav = function () {
        $nav.removeClass('is-open');
        $toggle.attr('aria-expanded', 'false');
    };

    $nav.on('click', 'a, button', function () {
        if (window.matchMedia('(max-width: 720px)').matches) {
            closeNav();
        }
    });

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape' && $nav.hasClass('is-open')) {
            closeNav();
            $toggle.trigger('focus');
        }
    });

    $(document).on('submit', 'form[data-confirm]', function (event) {
        if (!window.confirm($(this).attr('data-confirm'))) {
            event.preventDefault();
        }
    });

    // พรีวิวรูปภาพก่อนโพสต์ (หน้าสร้าง/แก้ไขประกาศเท่านั้น)
    var $fileInput = $('#image');
    var $preview = $('#image-preview');
    var $fileName = $('#image-name');
    var $removeImage = $('#remove_image');

    if ($fileInput.length) {
        $fileInput.on('change', function () {
            var file = this.files && this.files[0];

            if (file) {
                var reader = new FileReader();
                reader.onload = function (event) {
                    $preview.html('<img src="' + event.target.result + '" alt="ตัวอย่างภาพ">');
                };
                reader.readAsDataURL(file);

                if ($fileName.length) {
                    $fileName.text(file.name);
                }
                if ($removeImage.length) {
                    $removeImage.prop('checked', false);
                }
            }
        });

        if ($removeImage.length) {
            $removeImage.on('change', function () {
                if (this.checked) {
                    $fileInput.val('');
                    $fileName.text('ยังไม่เลือกไฟล์');
                }
            });
        }
    }

    // AJAX: ตรวจอีเมลซ้ำตอนออกจากช่องอีเมล (หน้าสมัครสมาชิกเท่านั้น)
    var $emailStatus = $('#email-status');

    if ($emailStatus.length) {
        $('#email').on('blur', function () {
            var email = $.trim(this.value);

            $emailStatus.removeClass('form-status-ok form-status-bad');

            if (email === '') {
                $emailStatus.text('');
                return;
            }

            $emailStatus.text('กำลังตรวจอีเมล...');

            $.getJSON('check_email.php', { email: email })
                .done(function (data) {
                    if (!data.valid) {
                        $emailStatus.text('รูปแบบอีเมลไม่ถูกต้อง').addClass('form-status-bad');
                    } else if (data.available) {
                        $emailStatus.text('อีเมลนี้ใช้ได้').addClass('form-status-ok');
                    } else {
                        $emailStatus.text('อีเมลนี้ถูกใช้งานแล้ว').addClass('form-status-bad');
                    }
                })
                .fail(function () {
                    $emailStatus.text('');
                });
        });
    }
});
