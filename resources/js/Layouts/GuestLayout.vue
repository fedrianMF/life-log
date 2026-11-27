<template>
    <main class="guest-shell min-vh-100 d-flex flex-row align-items-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                    <div class="card-group">
                        <div class="card">
                            <div class="card-body p-4 p-sm-5">
                                <slot />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
