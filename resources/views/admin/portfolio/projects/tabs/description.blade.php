<x-admin.tabs.wrapper>
    <x-slot:nav>
        @foreach(supported_languages_keys() as $lang)
            <x-admin.tabs.nav-item
                :is-active="$loop->first"
                :target="'description-locale-' . $lang"
                :title="$lang"
            />
        @endforeach
    </x-slot:nav>

    <x-slot:content>
        @foreach(supported_languages_keys() as $lang)
            <x-admin.tabs.pane :is-active="$loop->first" :id="'description-locale-' . $lang">
                @php
                    $fallbackTextBlock = [
                        'type' => 'text',
                        'content' => old('description.' . $lang, isset($project) ? $project->getTranslation('description', $lang) : null),
                    ];

                    $blocks = old(
                        'description_blocks.' . $lang,
                        isset($project)
                            ? ($project->getTranslation('description_blocks', $lang) ?: [$fallbackTextBlock])
                            : [$fallbackTextBlock]
                    );
                @endphp

                <div
                    class="d-flex flex-column gap-4"
                    data-project-description-builder
                    data-locale="{{ $lang }}"
                >
                    <div
                        class="d-flex flex-column gap-4"
                        data-blocks-wrapper
                    >
                        @foreach($blocks as $blockIndex => $block)
                            <div class="border rounded p-3 d-flex flex-column gap-3 bg-white" data-block>
                                <input type="hidden" name="description_blocks[{{ $lang }}][{{ $blockIndex }}][type]" value="{{ data_get($block, 'type', 'text') }}" data-block-type-input>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold me-1" data-block-label>Block</span>

                                    <select class="form-select w-auto" data-block-type-select>
                                        <option value="text" {{ data_get($block, 'type', 'text') === 'text' ? 'selected' : null }}>Text</option>
                                        <option value="floating_gallery" {{ data_get($block, 'type') === 'floating_gallery' ? 'selected' : null }}>Floating Image Gallery</option>
                                    </select>

                                    <x-admin.button
                                        data-block-add-after
                                        class="p-2 ms-auto"
                                        :btn="'btn-outline-success'"
                                        :iconName="'plus-circle'"
                                    />
                                    <x-admin.button
                                        data-block-remove
                                        class="p-2"
                                        :btn="'btn-outline-danger'"
                                        :iconName="'dash-circle'"
                                    />
                                    <x-admin.button
                                        data-block-move
                                        class="p-2"
                                        :btn="'btn-outline-secondary'"
                                        :iconName="'arrows-move'"
                                    />
                                    <button type="button" class="btn btn-outline-secondary p-2" data-block-toggle aria-expanded="false">
                                        <span data-block-toggle-icon>></span>
                                    </button>
                                </div>

                                <div class="d-flex flex-column gap-3 d-none" data-block-body>
                                    <div data-block-type-panel="text" class="{{ data_get($block, 'type', 'text') === 'text' ? null : 'd-none' }}">
                                        <x-admin.field.wysiwyg
                                            :name="'description_blocks['. $lang .'][' . $blockIndex . '][content]'"
                                            :placeholder="__('admin.description')"
                                            :value="data_get($block, 'content')"
                                            :height="300"
                                            :buttons="'blockquote|list|image|video'"
                                        />
                                    </div>

                                    <div data-block-type-panel="floating_gallery" class="d-flex flex-column gap-3 {{ data_get($block, 'type') === 'floating_gallery' ? null : 'd-none' }}">
                                        <div
                                            class="d-flex flex-column gap-3"
                                            data-gallery-items-wrapper
                                        >
                                            @foreach((data_get($block, 'items') ?: []) as $itemIndex => $item)
                                                <div class="border rounded p-3 d-flex flex-column gap-3" data-gallery-item>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <x-admin.button
                                                            data-gallery-item-move
                                                            class="p-2"
                                                            :btn="'btn-outline-secondary'"
                                                            :iconName="'arrows-move'"
                                                        />
                                                        <x-admin.button
                                                            data-gallery-item-add-after
                                                            class="p-2 ms-auto"
                                                            :btn="'btn-outline-success'"
                                                            :iconName="'plus-circle'"
                                                        />
                                                        <x-admin.button
                                                            data-gallery-item-remove
                                                            class="p-2"
                                                            :btn="'btn-outline-danger'"
                                                            :iconName="'dash-circle'"
                                                        />
                                                    </div>

                                                    <x-admin.field.image
                                                        :name="'description_blocks['. $lang .'][' . $blockIndex . '][items][' . $itemIndex . '][image_file]'"
                                                        :placeholder="'Image'"
                                                        :src="data_get($item, 'image')"
                                                        :required="false"
                                                        :ratio="'4x3'"
                                                        :fit="'contain'"
                                                    />
                                                    <input type="hidden" name="description_blocks[{{ $lang }}][{{ $blockIndex }}][items][{{ $itemIndex }}][image]" value="{{ data_get($item, 'image') }}" data-gallery-item-image-hidden>

                                                    <div class="row g-3">
                                                        <div class="col-12 col-lg-6">
                                                            <x-admin.field.text
                                                                :name="'description_blocks['. $lang .'][' . $blockIndex . '][items][' . $itemIndex . '][headline]'"
                                                                :value="data_get($item, 'headline')"
                                                                :required="false"
                                                                :placeholder="'Headline (optional)'"
                                                            />
                                                        </div>
                                                        <div class="col-12 col-lg-6">
                                                            <x-admin.field.text
                                                                :name="'description_blocks['. $lang .'][' . $blockIndex . '][items][' . $itemIndex . '][subhead]'"
                                                                :value="data_get($item, 'subhead')"
                                                                :required="false"
                                                                :placeholder="'Subhead (optional)'"
                                                            />
                                                        </div>
                                                        <div class="col-12 col-lg-6">
                                                            <x-admin.field.number
                                                                :name="'description_blocks['. $lang .'][' . $blockIndex . '][items][' . $itemIndex . '][col_span]'"
                                                                :value="data_get($item, 'col_span', 12)"
                                                                :placeholder="'Columns (1-12)'"
                                                                :fieldAttrs="'min=1 max=12'"
                                                            />
                                                        </div>
                                                        <div class="col-12 col-lg-6">
                                                            <x-admin.field.number
                                                                :name="'description_blocks['. $lang .'][' . $blockIndex . '][items][' . $itemIndex . '][col_start]'"
                                                                :value="data_get($item, 'col_start', 1)"
                                                                :placeholder="'Start column (1-12)'"
                                                                :fieldAttrs="'min=1 max=12'"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <x-admin.button
                                            data-gallery-item-add
                                            class="ms-auto"
                                            :btn="'btn-outline-primary'"
                                            :title="'Add image'"
                                            :iconName="'plus-circle'"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <x-admin.button
                        data-block-add
                        class="ms-auto"
                        :btn="'btn-outline-primary'"
                        :title="'Add block'"
                        :iconName="'plus-circle'"
                    />
                </div>

                <template data-block-template="text">
                    <div class="border rounded p-3 d-flex flex-column gap-3 bg-white" data-block>
                        <input type="hidden" name="description_blocks[{{ $lang }}][__block__][type]" value="text" data-block-type-input>

                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold me-1" data-block-label>Block</span>
                            <select class="form-select w-auto" data-block-type-select>
                                <option value="text" selected>Text</option>
                                <option value="floating_gallery">Floating Image Gallery</option>
                            </select>
                            <x-admin.button
                                data-block-add-after
                                class="p-2 ms-auto"
                                :btn="'btn-outline-success'"
                                :iconName="'plus-circle'"
                            />
                            <x-admin.button
                                data-block-remove
                                class="p-2"
                                :btn="'btn-outline-danger'"
                                :iconName="'dash-circle'"
                            />
                            <x-admin.button
                                data-block-move
                                class="p-2"
                                :btn="'btn-outline-secondary'"
                                :iconName="'arrows-move'"
                            />
                            <button type="button" class="btn btn-outline-secondary p-2" data-block-toggle aria-expanded="false">
                                <span data-block-toggle-icon>></span>
                            </button>
                        </div>

                        <div class="d-flex flex-column gap-3 d-none" data-block-body>
                            <div data-block-type-panel="text">
                                <x-admin.field.wysiwyg
                                    :name="'description_blocks['. $lang .'][__block__][content]'"
                                    :placeholder="__('admin.description')"
                                    :height="300"
                                    :buttons="'blockquote|list|image|video'"
                                />
                            </div>

                            <div data-block-type-panel="floating_gallery" class="d-flex flex-column gap-3 d-none">
                                <div class="d-flex flex-column gap-3" data-gallery-items-wrapper></div>
                                <x-admin.button
                                    data-gallery-item-add
                                    class="ms-auto"
                                    :btn="'btn-outline-primary'"
                                    :title="'Add image'"
                                    :iconName="'plus-circle'"
                                />
                            </div>
                        </div>
                    </div>
                </template>

                <template data-gallery-item-template>
                    <div class="border rounded p-3 d-flex flex-column gap-3" data-gallery-item>
                        <div class="d-flex align-items-center gap-2">
                            <x-admin.button
                                data-gallery-item-move
                                class="p-2"
                                :btn="'btn-outline-secondary'"
                                :iconName="'arrows-move'"
                            />
                            <x-admin.button
                                data-gallery-item-add-after
                                class="p-2 ms-auto"
                                :btn="'btn-outline-success'"
                                :iconName="'plus-circle'"
                            />
                            <x-admin.button
                                data-gallery-item-remove
                                class="p-2"
                                :btn="'btn-outline-danger'"
                                :iconName="'dash-circle'"
                            />
                        </div>

                        <x-admin.field.image
                            :name="'description_blocks['. $lang .'][__block__][items][__item__][image_file]'"
                            :placeholder="'Image'"
                            :required="false"
                            :ratio="'4x3'"
                            :fit="'contain'"
                        />
                        <input type="hidden" name="description_blocks[{{ $lang }}][__block__][items][__item__][image]" value="" data-gallery-item-image-hidden>

                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <x-admin.field.text
                                    :name="'description_blocks['. $lang .'][__block__][items][__item__][headline]'"
                                    :required="false"
                                    :placeholder="'Headline (optional)'"
                                />
                            </div>
                            <div class="col-12 col-lg-6">
                                <x-admin.field.text
                                    :name="'description_blocks['. $lang .'][__block__][items][__item__][subhead]'"
                                    :required="false"
                                    :placeholder="'Subhead (optional)'"
                                />
                            </div>
                            <div class="col-12 col-lg-6">
                                <x-admin.field.number
                                    :name="'description_blocks['. $lang .'][__block__][items][__item__][col_span]'"
                                    :value="12"
                                    :placeholder="'Columns (1-12)'"
                                    :fieldAttrs="'min=1 max=12'"
                                />
                            </div>
                            <div class="col-12 col-lg-6">
                                <x-admin.field.number
                                    :name="'description_blocks['. $lang .'][__block__][items][__item__][col_start]'"
                                    :value="1"
                                    :placeholder="'Start column (1-12)'"
                                    :fieldAttrs="'min=1 max=12'"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </x-admin.tabs.pane>
        @endforeach
    </x-slot:content>
</x-admin.tabs.wrapper>
