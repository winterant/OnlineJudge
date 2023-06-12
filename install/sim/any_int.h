/*	This file is part of the module ANY_INT.
	Written by Dick Grune, dick@dickgrune.com
	$Id: any_int.h,v 1.6 2023-05-08 10:43:39 dick Exp $
*/

#ifndef	_ANY_INT_H_
#define _ANY_INT_H_

/*	Printing size_t and very long ints.

   Printing integers using printf() requires specifying the format, which
   requires knowing the exact nature of the integer. But this is not always
   the case, f.e with size_t or extra-long integers for the accumulation of
   size_t values. Some systems use %z as a dedicated format to print size_t,
   but this is not portable since not all compilers know it.

   These problems are solved by introducing the type vlong_[u]int (see below),
   >>>> better name 'longest_int' ?? 0r "longest int" ??<<<<
   defined as the largest [unsigned] machine int type on the system,
   and routines to convert these to string.

   The resulting string is transient, but up to N_INDEPENDENT_CALLS calls
   can be used simultaneously.
   >>>> separate module ?? <<<<

   Since the value is passed to the conversion routines as a typed parameter
   the C compiler does the conversion (actually widening) for you.
*/

/* Public entries */
typedef long long int vlong_int;		/* largest int in the system */
#define	VLONG_INT_MAX	(9223372036854775807LL)
#define	VLONG_INT_MIN	((-VLONG_INT_MAX)-1)
typedef unsigned long long int vlong_uint;	/* largest uint in the system */
#define	VLONG_UINT_MAX	(18446744073709551615ULL)


/* transient * N_INDEPENDENT_CALLS */
extern const char *any_int2string(vlong_int val);
extern const char *any_uint2string(vlong_uint val);

#endif	/* _ANY_INT_H_ */
