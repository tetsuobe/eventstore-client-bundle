fromAll().when({
    $init: function (s, e) {
        return {count: 0}
    }, $any: function (s, e) {
        return {count: s.count + 1}
    }
})