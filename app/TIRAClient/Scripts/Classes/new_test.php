public $client;
    public $hostname;
    public $accessLogToken;
    //GuzzleHttp\Client
    public function __construct(GuzzleHttp\Client $client)
    {

        $this->client = $client;
        $this->hostname = $_ENV['BASE_URL'];
        
    }
    //get 
    public function getMembers(Request $request)
    {
        $url = $this->hostname . "/members";
        
        try {
            //make client json request
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $request->session()->get('accessLogToken'),
                ]

            ]);
            $members = json_decode($response->getBody());
            //store local
            $request->session()->put('members ', $members );

            return view('dashboard.pages.members.allMembers', ['members ' => $members ]);
        } catch (RequestException $e) {
            // To catch exactly error 401 use 
            if ($e->hasResponse()) {
                if ($e->getResponse()->getStatusCode() == '401') {
                    session()->flash('error', 'Session Exipired, Please Login Again.');
                    return redirect('/');
                } else {
                    session()->flash('error', 'Something went wrong, try again later.');
                    return redirect('/');
                }
            }
        } catch (\Exception $e) {

            session()->flash('error', 'Something went wrong, try again later.');
            return redirect('/');
        }
    }