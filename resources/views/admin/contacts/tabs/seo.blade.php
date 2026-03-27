<div class="grid gap-4">
    <div class="g-col-12 g-col-md-4">
        <!-- image -->
        <x-admin.field.image
            :name="'hero_image'"
            :placeholder="__('admin.image') . ' ( 16 / 9 )'"
            :ratio="'16x9'"
            :fit="'contain'"
            :src="$page->hasMedia('hero-image') ? $page->getFirstMediaUrl('hero-image', 'md-webp') : null"
            :required="!$page->hasMedia('hero-image')"
        />
    </div>

    <div class="g-col-12 g-col-md-8">
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
                            <!-- title -->
                            <x-admin.field.text
                                :name="'title['. $lang .']'"
                                :value="old('title.' . $lang, $page->getTranslation('title', $lang))"
                                :placeholder="__('admin.title')"
                            />

                            <!-- seo title -->
                            <x-admin.field.text
                                :name="'seo_title['. $lang .']'"
                                :value="old('seo_title.' . $lang, $page->getTranslation('seo_title', $lang))"
                                :placeholder="__('admin.seo_title')"
                                :required="false"
                            />

                            <!-- seo description -->
                            <x-admin.field.text
                                :name="'seo_description['. $lang .']'"
                                :value="old('seo_description.' . $lang, $page->getTranslation('seo_description', $lang))"
                                :placeholder="__('admin.seo_description')"
                                :required="false"
                            />

                            <!-- seo keywords -->
                            <x-admin.field.text
                                :name="'seo_keywords['. $lang .']'"
                                :value="old('seo_keywords.' . $lang, $page->getTranslation('seo_keywords', $lang))"
                                :placeholder="__('admin.seo_keywords')"
                                :required="false"
                            />

                            <!-- geo text -->
                            <x-admin.field.textarea
                                :name="'geo_text['. $lang .']'"
                                :value="old('geo_text.' . $lang, $page->getTranslation('geo_text', $lang))"
                                :placeholder="__('admin.geo_text')"
                                :required="false"
                            />
                        </div>
                    </x-admin.tabs.pane>
                @endforeach
            </x-slot:content>
        </x-admin.tabs.wrapper>
    </div>
</div>
