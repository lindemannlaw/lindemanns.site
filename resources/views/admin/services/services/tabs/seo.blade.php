<div class="d-flex flex-column gap-4">
    <x-admin.tabs.wrapper>
        <x-slot:nav>
            @foreach(supported_languages_keys() as $lang)
                <x-admin.tabs.nav-item
                    :is-active="$loop->first"
                    :target="'seo-locale-' . $lang"
                    :title="$lang"
                />
            @endforeach
        </x-slot:nav>

        <x-slot:content>
            @foreach(supported_languages_keys() as $lang)
                <x-admin.tabs.pane :is-active="$loop->first" :id="'seo-locale-' . $lang">
                    <div class="d-flex flex-column gap-4">
                        <!-- seo title -->
                        <x-admin.field.text
                            :name="'seo_title['. $lang .']'"
                            :value="old('seo_title.' . $lang, isset($service) ? $service->getTranslation('seo_title', $lang) : null)"
                            :placeholder="__('admin.seo_title')"
                            :required="false"
                        />

                        <!-- seo description -->
                        <x-admin.field.text
                            :name="'seo_description['. $lang .']'"
                            :value="old('seo_description.' . $lang, isset($service) ? $service->getTranslation('seo_description', $lang) : null)"
                            :placeholder="__('admin.seo_description')"
                            :required="false"
                        />

                        <!-- seo keywords -->
                        <x-admin.field.text
                            :name="'seo_keywords['. $lang .']'"
                            :value="old('seo_keywords.' . $lang, isset($service) ? $service->getTranslation('seo_keywords', $lang) : null)"
                            :placeholder="__('admin.seo_keywords')"
                            :required="false"
                        />

                        <!-- geo text -->
                        <x-admin.field.textarea
                            :name="'geo_text['. $lang .']'"
                            :value="old('geo_text.' . $lang, isset($service) ? $service->getTranslation('geo_text', $lang) : null)"
                            :placeholder="__('admin.geo_text')"
                            :required="false"
                        />
                    </div>
                </x-admin.tabs.pane>
            @endforeach
        </x-slot:content>
    </x-admin.tabs.wrapper>
</div>
