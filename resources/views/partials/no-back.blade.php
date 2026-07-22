<script>
    history.pushState(null, null, location.href);

    window.addEventListener('popstate', function () {
        history.pushState(null, null, location.href);
        window.location.href = "{{ route('login.pin') }}";
    });
</script>