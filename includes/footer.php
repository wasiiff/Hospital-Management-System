    <footer class="hms-footer text-center">
        <i class="bi bi-heart-pulse"></i> Hospital Management System
    </footer>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Turn native <select> controls into Select2 dropdowns. Opt out with data-no-select2.
$(function () {
    $('select:not([data-no-select2])').each(function () {
        var $el = $(this);
        $el.select2({
            theme: 'bootstrap-5',
            width: '100%',
            // search box only appears for longer lists
            minimumResultsForSearch: this.options.length > 8 ? 0 : Infinity,
            // render the dropdown next to the control so it isn't clipped by cards/tables
            dropdownParent: $el.parent()
        });
    });
});
</script>
</body>
</html>
