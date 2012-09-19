<!doctype html>

<head>
    <meta charset="utf-8">
    <title>CODIAD STYLE GUIDE</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/screen.css">
    <style>
        html { overflow: scroll; }
        body { width: 100%; margin: 0 auto; overflow: scroll; }
        td .icon { font-size: 30px; display: inline; padding-top: 0; margin-top: 0; }
        p { padding: 15px 0; margin: 0; font-weight: bold; }
        label { margin-top: 25px; }
        #container { width: 600px; margin: 50px auto; }
    </style>
</head>

<body>

    <div id="container">

    <label>Form Fields</label>
    
    <p>Code:</p>
    
    <pre>&lt;input type=&quot;text&quot;&gt;
    
&lt;select&gt;
    &lt;option value=&quot;one&quot;&lt;Option One&lt;/option&gt;
    &lt;option value=&quot;two&quot;&lt;Option Two&lt;/option&gt;
    &lt;option value=&quot;three&quot;&lt;Option Three&lt;/option&gt;
&lt;/select&gt;


&lt;textarea&gt;&lt;/textarea&gt;</pre>

    <p>Output:</p>
    
    <input type="text">
    
    <select>
        <option value="one">Option One</option>
        <option value="two">Option Two</option>
        <option value="three">Option Three</option>
    </select>
    
    <textarea></textarea>

    <label>Buttons</label>
    
    <p>Code:</p>
    
    <pre>&lt;button class=&quot;btn-left&quot;&gt;Left Button&lt;/button&gt;&lt;button class=&quot;btn-mid&quot;&gt;Mid Button&lt;/button&gt;&lt;button class=&quot;btn-right&quot;&gt;Right Button&lt;/button&gt;</pre>
    
    <p>Output:</p>
    
    <button class="btn-left">Left Button</button><button class="btn-mid">Mid Button</button><button class="btn-right">Right Button</button>
    
    
    <br><br>
    <label>Icons</label>

    <table style="font-weight: normal; width: 100%; margin: 0 auto;" cellpadding="5">
    
        <tr>
    
            <td>
            
            A : <span class="icon">A</span><br>
            B : <span class="icon">B</span><br>
            C : <span class="icon">C</span><br>
            D : <span class="icon">D</span><br>
            E : <span class="icon">E</span><br>
            F : <span class="icon">F</span><br>
            G : <span class="icon">G</span><br>
            H : <span class="icon">H</span><br>
            I : <span class="icon">I</span><br>
            J : <span class="icon">J</span><br>
            K : <span class="icon">K</span><br>
            L : <span class="icon">L</span><br>
            M : <span class="icon">M</span><br>
            N : <span class="icon">N</span><br>
            O : <span class="icon">O</span><br>
            P : <span class="icon">P</span><br>
            Q : <span class="icon">Q</span><br>
            R : <span class="icon">R</span><br>
            S : <span class="icon">S</span><br>
            T : <span class="icon">T</span><br>
            U : <span class="icon">U</span><br>
            V : <span class="icon">V</span><br>
            W : <span class="icon">W</span><br>
            X : <span class="icon">X</span><br>
            Y : <span class="icon">Y</span><br>
            Z : <span class="icon">Z</span><br>
            
            </td>
            
            <td>
            
            a : <span class="icon">a</span><br>
            b : <span class="icon">b</span><br>
            c : <span class="icon">c</span><br>
            d : <span class="icon">d</span><br>
            e : <span class="icon">e</span><br>
            f : <span class="icon">f</span><br>
            g : <span class="icon">g</span><br>
            h : <span class="icon">h</span><br>
            i : <span class="icon">i</span><br>
            j : <span class="icon">j</span><br>
            k : <span class="icon">k</span><br>
            l : <span class="icon">l</span><br>
            m : <span class="icon">m</span><br>
            n : <span class="icon">n</span><br>
            o : <span class="icon">o</span><br>
            p : <span class="icon">p</span><br>
            q : <span class="icon">q</span><br>
            r : <span class="icon">r</span><br>
            s : <span class="icon">s</span><br>
            t : <span class="icon">t</span><br>
            u : <span class="icon">u</span><br>
            v : <span class="icon">v</span><br>
            w : <span class="icon">w</span><br>
            x : <span class="icon">x</span><br>
            y : <span class="icon">y</span><br>
            z : <span class="icon">z</span><br>
            
            </td>
            
            <td>
            
            0 : <span class="icon">0</span><br>
            1 : <span class="icon">1</span><br>
            2 : <span class="icon">2</span><br>
            3 : <span class="icon">3</span><br>
            4 : <span class="icon">4</span><br>
            5 : <span class="icon">5</span><br>
            6 : <span class="icon">6</span><br>
            7 : <span class="icon">7</span><br>
            8 : <span class="icon">8</span><br>
            9 : <span class="icon">9</span><br>
            ! : <span class="icon">!</span><br>
            @ : <span class="icon">@</span><br>
            # : <span class="icon">#</span><br>
            $ : <span class="icon">$</span><br>
            % : <span class="icon">%</span><br>
            ^ : <span class="icon">^</span><br>
            &amp; : <span class="icon">&amp;</span><br>
            * : <span class="icon">*</span><br>
            ( : <span class="icon">(</span><br>
            ) : <span class="icon">)</span><br>
            - : <span class="icon">-</span><br>
            _ : <span class="icon">_</span><br>
            + : <span class="icon">+</span><br>
            = : <span class="icon">=</span><br>
            \ : <span class="icon">\</span><br>
            | : <span class="icon">|</span><br>
            
            </td>
            
            <td>
            
            ~ : <span class="icon">~</span><br>
            ` : <span class="icon">`</span><br>
            &gt; : <span class="icon">&gt;</span><br>
            &lt; : <span class="icon">&lt;</span><br>
            , : <span class="icon">,</span><br>
            . : <span class="icon">.</span><br>
            ? : <span class="icon">?</span><br>
            / : <span class="icon">/</span><br>
            { : <span class="icon">{</span><br>
            } : <span class="icon">}</span><br>
            [ : <span class="icon">[</span><br>
            ] : <span class="icon">]</span><br>
            : : <span class="icon">:</span><br>
            ; : <span class="icon">;</span><br>
            " : <span class="icon">"</span><br>
            ' : <span class="icon">'</span><br>

            
            </td>
            
        </tr>
        
    </table>
    
    </div>

</body>
</html>