<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
<script>
    (function () {
        const pendingForms = new WeakSet();

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'get' || form.hasAttribute('data-skip-confirm')) return;

            if (form.dataset.confirmApproved === 'true') {
                delete form.dataset.confirmApproved;
                return;
            }

            event.preventDefault();
            if (pendingForms.has(form)) return;
            pendingForms.add(form);

            const isDelete = form.querySelector('input[name="_method"][value="DELETE"]') !== null;
            const submitter = event.submitter;
            const continueSubmit = function () {
                pendingForms.delete(form);
                form.dataset.confirmApproved = 'true';
                if (submitter && submitter.form === form) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            };
            const cancelSubmit = function () {
                pendingForms.delete(form);
            };

            if (typeof Swal === 'undefined') {
                if (window.confirm(isDelete ? 'ยืนยันลบรายการนี้หรือไม่?' : 'ยืนยันส่งแบบฟอร์มหรือไม่?')) {
                    continueSubmit();
                } else {
                    cancelSubmit();
                }
                return;
            }

            Swal.fire({
                title: form.dataset.confirmTitle || (isDelete ? 'ยืนยันการลบรายการ?' : 'ยืนยันการส่งข้อมูล?'),
                text: form.dataset.confirmText || (isDelete ? 'เมื่อลบแล้วจะไม่สามารถกู้คืนได้' : 'กรุณายืนยันก่อนส่งแบบฟอร์ม'),
                icon: isDelete ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: isDelete ? '#C62828' : '#00695C',
                cancelButtonColor: '#78909C',
                confirmButtonText: form.dataset.confirmButton || (isDelete ? 'ลบรายการ' : 'ยืนยัน'),
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    continueSubmit();
                } else {
                    cancelSubmit();
                }
            });
        }, true);
    })();
</script>