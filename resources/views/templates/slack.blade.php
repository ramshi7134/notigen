{
"blocks": [
{
"type": "header",
"text": {
"type": "plain_text",
"text": "{{ $header ?? '' }}"
}
},
{
"type": "section",
"text": {
"type": "mrkdwn",
"text": "{{ $content }}"
}
}
@if (isset($fields))
    ,{
    "type": "section",
    "fields": @json($fields)
    }
@endif
@if (isset($actionUrl) && isset($actionText))
    ,{
    "type": "actions",
    "elements": [
    {
    "type": "button",
    "text": {
    "type": "plain_text",
    "text": "{{ $actionText }}"
    },
    "url": "{{ $actionUrl }}"
    }
    ]
    }
@endif
]
}
