<?php

namespace App\Http\Middleware;

use Closure;

class OrderByCorrections
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data = $request->all();
        $mergeArray = [];

        foreach ($data as $key => $val) {
            switch ($key) {
                case "sortBy":
                    if ($val == 'invoice_type_name') {
                        $mergeArray = [
                            'sortBy' => 'invoice_type'
                        ];
                    }
                    break;
            }
        }

        if (count($mergeArray) > 0) {
            $request->merge($mergeArray);
        }

        return $next($request);

    }

}
