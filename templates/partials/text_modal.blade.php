<div class="grid mod-guide-text u-mb-2" {!! isset($item['toggle_key']) && !empty($item['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $item['toggle_key'] . '"' : '' !!}>
    <div class="grid-xs-12">
        <article>
            @typography([
                'element' => 'h3',
            ])
                {!! $content['title'] !!}
            @endtypography

            @typography([
                'element' => 'meta',
            ])
                {!! $content['summary'] !!}
            @endtypography

            @button([
                'text' => $content['title'],
                'href' => '',
                'icon' => 'open_with',
                'size' => 'sm',
                'color' => 'default',
                'style' => 'filled',
                'reversePositions' => true,
                'attributeList' => ['data-open' => "mod-guide-modal-".$stepId]
            ])
            @endbutton

        </article>

        @modal([
            'heading' => $content['title'],
            'isPanel' => false,
            'id' => "mod-guide-modal-".$stepId,
            'overlay' => 'dark',
            'animation' => 'scale-up',
            'attributeList' => [
                'tabindex' => "-1",
                'role' => "dialog",
                'aria-hidden' => "true"
            ]
        ])
            {!! $content['modal_content'] !!}
        
        @endmodal

    </div>
</div>
