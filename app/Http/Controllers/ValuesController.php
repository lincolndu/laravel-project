<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Values;
use Session;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Redis;


class ValuesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         if(Session::has('success_msg')){
            $msg = Session::get('success_msg');
            Session::forget('success_msg');
            echo "<h3>" . $msg ."</h3>".PHP_EOL;
         } 
            

         $keys=array_filter(explode(',', request('keys')));
         $values=[];

        if ($keys) {
            return $keys;
            foreach ($keys as $key) {
                $values[] = $val= Redis::get($key);
                Redis::set($key, $val,'EX',300);
            }            
        }else{
            $keys= Redis::keys('*');
            if (!empty($keys)) {
                $values = Redis::mGet($keys);
            }           
        }
        $data=array_filter(array_combine($keys,$values));

        return response()->json($data,200)->header('Content-Type', 'text/json');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function insertForm()
    {
        return view('api.insert');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Redis::set($request->key, $request->value,'EX',300); 
        // header( "refresh:5;url=values" );
        Session::flash('success_msg', 'Successfully data inserted');
        return redirect('values',201)->with('status', 'Successfully data inserted!');     
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($key)
    {
        if (Redis::exists($key)) {
            return view('api.edit',get_defined_vars());
        }else{
            return redirect('/values',200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $key)
    {
        if (Redis::exists($key)) {
            Redis::set($key, $request->value,'EX',300); 
            Session::flash('success_msg', 'Successfully data updated');
            return redirect('values',204)->with('status', 'Successfully data updated!');   
        }
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return 'delete route';
    }
}
