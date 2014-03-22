@foreach ($logs as $log)
<p>
	Time: {{ $log['time'] }}<br>
	URL: {{ $log['url']  }}<br>
	Route: {{ $log['route']  }}<br>
	Client: {{ $log['client']  }}
</p>

<p>
	{{ $log['exception']['class'] }}<br>
	@if ($log['exception']['message'])
	Exception message: {{ $log['exception']['message'] }}<br>
	@endif
	@if ($log['exception']['code'] > 0)
	Exception code: {{ $log['exception']['code'] }}<br>
	@endif
	In {{ $log['exception']['file'] }} on line {{ $log['exception']['line'] }}
</p>

@if (!empty($log['input']))
<p>
	<b>Input</b><br>
	<?php var_dump($log['input']) ?>
</p>
@endif
<hr>
@endforeach