@php($translatePrefix='env-editor::env-editor.views.currentEnv.')
<template id="env-editor-config-actions">
    <div>
        <button class="btn-outline-dark btn btn-sm" title="{{__($translatePrefix.'btn.deleteConfigCacheDesc')}}"
                @click="deleteConfigCache">{{__($translatePrefix.'btn.deleteConfigCache')}}</button>

    </div>
</template>

@push('scripts')

    <script>

        let configActions = {
            template: '#env-editor-config-actions',
            methods: {
                deleteConfigCache() {
                    this.submit('delete', '{{route(config($package.'.route.name').'.clearConfigCache')}}');
                },
                submit(method, url) {
                    axios({
                        method: method,
                        _token: '{{csrf_token()}}',
                        url: url,
                        data: this.modalItem
                    }).then((response) => {
                        if (response.data.message) {
                            envAlert('info', response.data.message);
                        }
                    }).catch((error) => {
                        envAlert('danger', error.response.data.message);
                    })
                },
            }
        };

    </script>
@endpush
