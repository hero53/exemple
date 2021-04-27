public function index()
    {
        $data['page_title'] = "Liste des démarcheurs";
        $data['section_title'] ="acconier";
        $data['menu'] ="acconier";
        $data['title'] = " liste des acconiers";
        User::logs("liste du démarcheur ");
        $data['acconiers'] = Acconier::orderBy('created_at','DESC')->paginate(50);
        return  view('acconiers.index', $data);
    }


    public function search(Request $request){
        User::logs("Affichage des acconiers");


        $name = $request->name ?? '';
        $from = $request->from ?? date('Y-m-d', strtotime(0)); // 1970-01-01
        $to = Carbon::parse($request->to)->addDay();

        $data['acconiers'] = Acconier::whereBetween('created_at', [$from, $to])
            ->where('nom', 'LIKE', "%$name%")->orWhere('prenoms', 'LIKE', "%$name%")
            ->orderBy('nom')
            ->paginate(100);

        $data['acconiers']->appends([
            'name' => $request->name,
            'from' => $request->from,
            'to' => $request->to,
        ]);

        $data['page_title'] = "Liste des démarcheurs";
        $data['section_title'] ="acconier";
        $data['menu'] ="acconier";
        $data['title'] = " liste des démarcheur";

        return view('acconiers.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['page_title'] = "creation d'un démarcheur";
        $data['section_title'] ="acconier";
        $data['title'] = "creation d'un démarcheur";
        $data['menu'] ="acconier";
        User::logs("Enregistrement du démarcheur ");

        return  view('acconiers.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nom' => 'required',
            'prenoms' => 'required',
            'contact' => 'required',
            'adresse' => 'nullable',
            'email' => 'required|email|unique:acconiers',
            'pourcentage' => 'nullable|integer',
        ]);

        $acconier = new Acconier;

        $acconier->nom = htmlspecialchars($request->nom);
        $acconier->prenoms = htmlspecialchars($request->prenoms);
        $acconier->contact = htmlspecialchars($request->contact);
        $acconier->adresse = htmlspecialchars($request->adresse);
        $acconier->email = htmlspecialchars($request->email);
        $acconier->pourcentage = htmlspecialchars($request->pourcentage);
        $acconier->created_by = auth()->user()->nom.' '.auth()->user()->prenoms;

        User::logs("Enregistrement du démarcheur ".htmlspecialchars($request->nom)." ".htmlspecialchars($request->prenoms));

        $acconier->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Démarcheur créé avec succès!');

        return redirect()->route('acconiers.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\acconier  $acconier
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $id = htmlspecialchars($id);

        $data['acconier'] = Acconier::find($id);

        if(empty($data['acconier'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Démarcheur introuvable');

            return back();
        }

        $data['page_title'] = "detail d'un démarcheurs";
        $data['section_title'] ="acconier";
        $data['title'] = "detail d'un démarcheurs";
        $data['menu'] ="acconier";

        User::logs("Modification du démarcheur ".htmlspecialchars($data['acconier']->nom)." ".htmlspecialchars($data['acconier']->prenoms));

        return view('acconiers.show',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\acconier  $acconier
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = htmlspecialchars($id);

        $data['acconier'] = Acconier::find($id);

        if(empty($data['acconier'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Démarcheur introuvable');

            return back();
        }

        $data['page_title'] = "modification d'un démarcheurs";
        $data['section_title'] ="acconier";
        $data['title'] = "modification d'un démarcheurs";
        $data['menu'] ="acconier";

        User::logs("Modification du démarcheur ".htmlspecialchars($data['acconier']->nom)." ".htmlspecialchars($data['acconier']->prenoms));

        return view('acconiers.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\acconier  $acconier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'nom' => 'required',
            'prenoms' => 'required',
            'contact' => 'required',
            'adresse' => 'nullable',
            'email' => 'required|email',
            'pourcentage' => 'nullable|integer',
        ]);

        $id = htmlspecialchars($request->id);

        $acconier =  Acconier::find($id);

        if(empty($acconier)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Démarcheur introuvable');

            return back();
        }

        $acconier->nom = htmlspecialchars($request->nom);
        $acconier->prenoms = htmlspecialchars($request->prenoms);
        $acconier->contact = htmlspecialchars($request->contact);
        $acconier->adresse = htmlspecialchars($request->adresse);
        $acconier->email = htmlspecialchars($request->email);
        $acconier->pourcentage = htmlspecialchars($request->pourcentage);
        $acconier->created_by = auth()->user()->nom.' '.auth()->user()->prenoms;

        User::logs("Modification du démarcheur ".htmlspecialchars($request->nom)." ".htmlspecialchars($request->prenoms));

        $acconier->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Démarcheur modifié avec succès!');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\acconier  $acconier
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = htmlspecialchars($id);

        $acconier = Acconier::find($id);

        if(empty($acconier)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Démarcheur introuvable');

            return back();
        }

        User::logs("Modification du démarcheur ".htmlspecialchars($acconier->nom)." ".htmlspecialchars($acconier->prenoms));
        $acconier->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Démarcheur supprimé avec succèss');

        return back();

    }

    public function print(Request $request)
    {
        $name = $request->name ?? '';
        $from = $request->from ?? date('Y-m-d', strtotime(0)); // 1970-01-01
        $to = Carbon::parse($request->to)->addDay();

        $data['acconiers'] = Acconier::whereBetween('created_at', [$from, $to])
            ->where(function($q) use($name) {
                $q->where('nom', 'LIKE', "%$name%")->orWhere('prenoms', 'LIKE', "%$name%");
            })
            ->orderBy('nom')
            ->get();
        //return view('armateurs.print', $data);
        $pdf = PDF::loadView('acconiersprint', $data )->setPaper('a4', 'landscape');
        $name = "Liste-armateurs.pdf";
        User::logs("Impression de la fiche de l'armateur");
        return $pdf->stream($name);
    }